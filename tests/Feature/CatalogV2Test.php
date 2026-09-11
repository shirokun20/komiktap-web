<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CatalogV2Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_comics_returns_envelope_with_pagination()
    {
        Http::fake([
            'komiktap.info/*' => Http::response([
                'data' => [
                    ['id' => 'a', 'title' => 'A', 'genres' => ['Drama'], 'coverUrl' => 'https://komiktap.info/wp-content/a.webp'],
                ],
                'pagination' => ['page' => 1, 'perPage' => 20, 'total' => 1, 'totalPages' => 1],
            ], 200),
        ]);

        $response = $this->getJson('/api/v2/catalog/comics');

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'pagination' => ['page' => 1, 'perPage' => 20, 'total' => 1, 'totalPages' => 1],
                ],
            ])
            ->assertJsonPath('data.items.0.id', 'a');
        // coverUrl ditulis ulang ke proxy lokal.
        $this->assertStringStartsWith(url('/api/v2/catalog/image'), $response->json('data.items.0.coverUrl'));
    }

    public function test_comics_rejects_invalid_params_without_upstream_call()
    {
        $response = $this->getJson('/api/v2/catalog/comics?perPage=101&orderBy=acak');

        $response->assertStatus(422);
        Http::assertNothingSent();
    }

    public function test_comics_passes_upstream_through_with_all_20_items()
    {
        $items = [];
        for ($i = 1; $i <= 20; $i++) {
            $items[] = ['id' => 'item-'.$i, 'title' => 'T'.$i, 'genres' => ['Drama', 'Mature']];
        }

        Http::fake([
            'komiktap.info/*' => Http::response([
                'data' => $items,
                'pagination' => ['page' => 1, 'perPage' => 20, 'total' => 4942, 'totalPages' => 248],
            ], 200),
        ]);

        $response = $this->getJson('/api/v2/catalog/comics');

        $response->assertOk()
            ->assertJsonCount(20, 'data.items')
            ->assertJsonPath('data.pagination.total', 4942)
            ->assertJsonPath('data.pagination.totalPages', 248);
        Http::assertSentCount(1);
    }

    public function test_announcements_returns_items()
    {
        Http::fake([
            'komiktap.info/*' => Http::response([
                ['title' => 'Info', 'message' => 'Halo', 'date' => '01 Sep 2026'],
            ], 200),
        ]);

        $response = $this->getJson('/api/v2/catalog/announcements');

        $response->assertOk()
            ->assertJson(['status' => 'success'])
            ->assertJsonPath('data.0.title', 'Info');
    }

    public function test_detail_strips_chapter_pages_and_proxies_cover()
    {
        Http::fake([
            'komiktap.info/*' => Http::response([
                'id' => 'flesh-and-money',
                'title' => 'Flesh and Money',
                'genres' => ['Drama'],
                'coverUrl' => 'https://komiktap.info/wp-content/x.webp',
                'chapters' => [
                    ['id' => 'flesh-and-money-chapter-1', 'number' => 1, 'title' => 'Chapter 1', 'pages' => ['https://cdn/x.jpg']],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/v2/catalog/comics/flesh-and-money');

        $response->assertOk()
            ->assertJsonPath('data.id', 'flesh-and-money')
            ->assertJsonMissingPath('data.chapters.0.pages');
        $this->assertStringStartsWith(url('/api/v2/catalog/image'), $response->json('data.coverUrl'));
    }

    public function test_detail_not_found_returns_json_404()
    {
        Http::fake([
            'komiktap.info/*' => Http::response('<html><head><title>404 Not Found</title></head><body>nginx</body></html>', 404, ['Content-Type' => 'text/html']),
        ]);

        $response = $this->getJson('/api/v2/catalog/comics/xxx-tidak-ada-123');

        $response->assertStatus(404)->assertJson(['status' => 'failed']);
        $this->assertStringNotContainsString('<html', $response->getContent());
    }

    public function test_chapter_returns_proxied_page_urls()
    {
        Http::fake([
            'komiktap.info/*' => Http::response([
                'id' => 'dewasa-chapter-1',
                'number' => 1,
                'title' => 'Chapter 1',
                'pages' => ['https://cdn.uqni.net/images/26/x/1/001.jpg', 'https://cdn.uqni.net/images/26/x/1/002.jpg'],
            ], 200),
        ]);

        $response = $this->getJson('/api/v2/catalog/comics/dewasa/chapters/1');

        $response->assertOk();
        $pages = $response->json('data.pages');
        $this->assertCount(2, $pages);
        foreach ($pages as $page) {
            $this->assertStringStartsWith(url('/api/v2/catalog/image'), $page);
        }
    }

    public function test_chapter_not_found_returns_json_404()
    {
        Http::fake([
            'komiktap.info/*' => Http::response('<html><head><title>404 Not Found</title></head><body>nginx</body></html>', 404, ['Content-Type' => 'text/html']),
        ]);

        $response = $this->getJson('/api/v2/catalog/comics/a/chapters/9999');

        $response->assertStatus(404)->assertJson(['status' => 'failed']);
    }

    public function test_image_endpoint_proxies_bytes()
    {
        $fakeJpeg = "\xFF\xD8\xFF fake-jpeg-bytes";

        Http::fake([
            'komiktap.info/*' => Http::response($fakeJpeg, 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $url = 'https://komiktap.info/wp-content/uploads/2025/11/x.webp';
        $response = $this->get('/api/v2/catalog/image?u='.rawurlencode($url));

        $response->assertOk();
        $this->assertSame('image/jpeg', $response->headers->get('Content-Type'));
        $this->assertSame($fakeJpeg, $response->getContent());
    }

    public function test_image_endpoint_rejects_disallowed_host()
    {
        $response = $this->getJson('/api/v2/catalog/image?u='.rawurlencode('https://evil.com/x.jpg'));

        $response->assertStatus(403)->assertJson(['status' => 'failed']);
        Http::assertNothingSent();
    }

    public function test_genre_endpoint_maps_slug_to_search()
    {
        Http::fake(function ($request) {
            // Pastikan slug "martial-arts" diteruskan sebagai search "martial arts".
            $this->assertSame('martial arts', $request->data()['search'] ?? null);

            return Http::response([
                'data' => [
                    ['id' => 'a', 'title' => 'A', 'genres' => ['Martial Arts'], 'coverUrl' => 'https://komiktap.info/wp-content/a.webp'],
                ],
                'pagination' => ['page' => 1, 'perPage' => 20, 'total' => 11, 'totalPages' => 1],
            ], 200);
        });

        $response = $this->getJson('/api/v2/catalog/genres/martial-arts');

        $response->assertOk()
            ->assertJsonPath('data.genre', 'martial arts')
            ->assertJsonPath('data.items.0.id', 'a')
            ->assertJsonPath('data.pagination.total', 11);
    }

    public function test_genre_endpoint_rejects_invalid_slug()
    {
        $response = $this->getJson('/api/v2/catalog/genres/INVALID_SLUG');

        $response->assertStatus(404);
        Http::assertNothingSent();
    }

    public function test_genres_index_returns_slug_name_count()
    {
        Http::fake([
            'komiktap.info/genres/' => Http::response(
                '<html><body><ul class="taxindex">'
                .'<li><a href="https://komiktap.info/genres/action/"><span>Action</span> <i>155</i></a></li>'
                .'<li><a href="https://komiktap.info/genres/adult/"><span>Adult</span> <i>2550</i></a></li>'
                .'</ul></body></html>',
                200,
                ['Content-Type' => 'text/html']
            ),
        ]);

        $response = $this->getJson('/api/v2/catalog/genres');

        $response->assertOk()
            ->assertJsonPath('data.0.slug', 'action')
            ->assertJsonPath('data.0.name', 'Action')
            ->assertJsonPath('data.0.count', 155)
            ->assertJsonPath('data.1.slug', 'adult')
            ->assertJsonPath('data.1.count', 2550);
    }

    public function test_az_returns_letters_items_pagination()
    {
        Http::fake([
            'komiktap.info/a-z-list*' => Http::response(
                '<html><body>'
                .'<div class="lista"><a href="https://komiktap.info/a-z-list/?show=A">A</a>'
                .'<a href="https://komiktap.info/a-z-list/?show=B">B</a></div>'
                .'<div class="bs"><div class="bsx"><a href="https://komiktap.info/manga/solo-leveling/" title="Solo Leveling">'
                .'<img src="https://komiktap.info/wp-content/solo.webp"/></a></div></div>'
                .'<div class="pagination"><span class="page-numbers current">1</span>'
                .' <a class="page-numbers" href="https://komiktap.info/a-z-list/page/2/?show=A">2</a>'
                .' <a class="next page-numbers" href="https://komiktap.info/a-z-list/page/2/?show=A">Next</a></div>'
                .'</body></html>',
                200,
                ['Content-Type' => 'text/html']
            ),
        ]);

        $response = $this->getJson('/api/v2/catalog/az?letter=A&page=1');

        $response->assertOk()
            ->assertJsonPath('data.letter', 'A')
            ->assertJsonPath('data.items.0.id', 'solo-leveling')
            ->assertJsonPath('data.items.0.title', 'Solo Leveling')
            ->assertJsonPath('data.pagination.page', 1)
            ->assertJsonPath('data.pagination.totalPages', 2)
            ->assertJsonPath('data.pagination.hasNext', true);
        $this->assertContains('A', $response->json('data.letters'));
        $this->assertStringStartsWith(url('/api/v2/catalog/image'), $response->json('data.items.0.coverUrl'));
    }

    public function test_az_rejects_invalid_letter()
    {
        $response = $this->getJson('/api/v2/catalog/az?letter=AB');

        $response->assertStatus(422);
        Http::assertNothingSent();
    }
}
