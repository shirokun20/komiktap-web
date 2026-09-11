<?php

namespace Tests\Unit;

use App\Exceptions\CatalogException;
use App\Services\CatalogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CatalogServiceTest extends TestCase
{
    protected function service(): CatalogService
    {
        Cache::flush();

        return new CatalogService();
    }

    public function test_image_url_rewrites_allowed_hosts_to_proxy()
    {
        $service = $this->service();

        $proxied = $service->imageUrl('https://komiktap.info/wp-content/uploads/2025/11/x.webp');

        $this->assertStringStartsWith(url('/api/v2/catalog/image').'?u=', $proxied);
        $this->assertStringContainsString(rawurlencode('https://komiktap.info/wp-content/uploads/2025/11/x.webp'), $proxied);
    }

    public function test_image_url_leaves_disallowed_hosts_untouched()
    {
        $service = $this->service();

        $this->assertSame('https://example.com/x.jpg', $service->imageUrl('https://example.com/x.jpg'));
        $this->assertSame('', $service->imageUrl(''));
        $this->assertSame('not-a-url', $service->imageUrl('not-a-url'));
    }

    public function test_comics_passes_upstream_through_untouched()
    {
        $items = [];
        for ($i = 1; $i <= 20; $i++) {
            $items[] = ['id' => 'item-'.$i, 'genres' => ['Drama', 'Mature']];
        }

        Http::fake([
            'komiktap.info/*' => Http::response([
                'data' => $items,
                'pagination' => ['page' => 1, 'perPage' => 20, 'total' => 4942, 'totalPages' => 248],
            ], 200),
        ]);

        $result = $this->service()->comics(['search' => '', 'page' => 1, 'perPage' => 20, 'orderBy' => 'lastUpdated', 'order' => 'desc']);

        $this->assertCount(20, $result['items']);
        $this->assertSame(4942, $result['pagination']['total']);
        Http::assertSentCount(1);
    }

    public function test_comics_uses_cache_on_second_call()
    {
        $items = [];
        for ($i = 1; $i <= 20; $i++) {
            $items[] = ['id' => 'a-'.$i, 'genres' => ['Drama']];
        }

        Http::fake([
            'komiktap.info/*' => Http::response([
                'data' => $items,
                'pagination' => ['page' => 1, 'perPage' => 20, 'total' => 20, 'totalPages' => 1],
            ], 200),
        ]);

        $service = $this->service();
        $params = ['search' => '', 'page' => 1, 'perPage' => 20, 'orderBy' => 'lastUpdated', 'order' => 'desc'];

        $first = $service->comics($params);
        $second = $service->comics($params);

        $this->assertSame($first, $second);
        Http::assertSentCount(1);
    }

    public function test_fetch_image_rejects_disallowed_host()
    {
        try {
            $this->service()->fetchImage('https://evil.com/x.jpg');
            $this->fail('Expected CatalogException was not thrown.');
        } catch (CatalogException $e) {
            $this->assertSame(403, $e->status());
        }
    }

    public function test_fetch_image_rejects_non_image_content()
    {
        Http::fake([
            '*' => Http::response('<html></html>', 200, ['Content-Type' => 'text/html']),
        ]);

        try {
            $this->service()->fetchImage('https://komiktap.info/x.jpg');
            $this->fail('Expected CatalogException was not thrown.');
        } catch (CatalogException $e) {
            $this->assertSame(502, $e->status());
        }
    }

    public function test_html_404_maps_to_catalog_not_found()
    {
        Http::fake([
            'komiktap.info/*' => Http::response('<html><head><title>404 Not Found</title></head><body>nginx</body></html>', 404, ['Content-Type' => 'text/html']),
        ]);

        $this->expectException(CatalogException::class);
        $this->expectExceptionMessage('Katalog tidak ditemukan.');

        $this->service()->detail('xxx-tidak-ada-123');
    }

    public function test_timeout_maps_to_504()
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('timeout');
        });

        try {
            $this->service()->announcements();
            $this->fail('Expected CatalogException was not thrown.');
        } catch (CatalogException $e) {
            $this->assertSame(504, $e->status());
            $this->assertSame('Katalog tidak tersedia sementara.', $e->getMessage());
        }
    }
}
