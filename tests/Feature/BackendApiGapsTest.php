<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BackendApiGapsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function siteHtml(array $cards, int $totalPages = 3): string
    {
        $items = '';
        foreach ($cards as $c) {
            $items .= '<div class="bs"><div class="bsx">'
                .'<a href="https://komiktap.info/manga/'.$c['id'].'/" title="'.$c['title'].'">'
                .'<div class="limit"><span class="status '.($c['status'] ?? 'Ongoing').'">'.($c['status'] ?? 'Ongoing').'</span>'
                .' <span class="type '.($c['type'] ?? 'Manhwa').'"></span>'
                .' <img src="https://komiktap.info/wp-content/'.$c['id'].'.jpg"/></div>'
                .'<div class="bigor"><div class="tt">'.$c['title'].'</div>'
                .'<div class="adds"><div class="epxs">Chapter 12</div>'
                .'<div class="rt"><div class="numscore">7.6</div></div></div></div>'
                .'</a></div></div>';
        }

        return '<html><body><div class="listupd cp">'.$items.'</div>'
            .'<div class="pagination"><span class="page-numbers current">1</span>'
            .' <a class="page-numbers" href="https://komiktap.info/list-manhwa/page/'.$totalPages.'/">'.$totalPages.'</a></div>'
            .'</body></html>';
    }

    public function test_comics_type_filter_returns_only_matching_type()
    {
        Http::fake([
            'komiktap.info/list-manhwa*' => Http::response($this->siteHtml([
                ['id' => 'a', 'title' => 'A', 'type' => 'Manhwa', 'status' => 'Ongoing'],
                ['id' => 'b', 'title' => 'B', 'type' => 'Manhwa', 'status' => 'Completed'],
            ]), 200, ['Content-Type' => 'text/html']),
            'komiktap.info/*' => Http::response(['data' => [], 'pagination' => []], 200),
        ]);

        $response = $this->getJson('/api/v2/catalog/comics?type=manhwa&status=completed');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', 'b')
            ->assertJsonPath('data.items.0.type', 'Manhwa')
            ->assertJsonPath('data.items.0.status', 'Completed');
    }

    public function test_comics_rejects_invalid_type_without_source_call()
    {
        $response = $this->getJson('/api/v2/catalog/comics?type=doujin');

        $response->assertStatus(400)
            ->assertJsonPath('status', 'failed');
        Http::assertNothingSent();
    }

    public function test_comics_filter_empty_returns_200_with_empty_items()
    {
        Http::fake([
            'komiktap.info/*' => Http::response('<html><body><div class="listupd cp"></div></body></html>', 200, ['Content-Type' => 'text/html']),
        ]);

        $response = $this->getJson('/api/v2/catalog/comics?type=manga');

        $response->assertOk()->assertJsonPath('data.items', []);
    }

    public function test_projects_returns_items_with_pagination()
    {
        Http::fake([
            'komiktap.info/project*' => Http::response($this->siteHtml([
                ['id' => 'p1', 'title' => 'P1', 'type' => 'Manga', 'status' => 'Ongoing'],
            ], 5), 200, ['Content-Type' => 'text/html']),
            'komiktap.info/*' => Http::response('x', 404),
        ]);

        $response = $this->getJson('/api/v2/catalog/projects?page=1&perPage=2');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.items.0.id', 'p1')
            ->assertJsonPath('data.pagination.page', 1);
    }

    public function test_projects_out_of_range_returns_200_empty()
    {
        Http::fake([
            'komiktap.info/project*' => Http::response('Not Found', 404),
        ]);

        $this->getJson('/api/v2/catalog/projects?page=99')->assertOk()->assertJsonPath('data.items', []);
    }

    public function test_projects_rejects_invalid_params_without_source_call()
    {
        $this->getJson('/api/v2/catalog/projects?perPage=101')->assertStatus(422);
        Http::assertNothingSent();
    }

    private function makeTx(User $user, string $status, array $extra = []): Transaction
    {
        return Transaction::create(array_merge([
            'customer_contact' => $user->email,
            'plan_name' => 'Premium',
            'device_quota' => 3,
            'duration_months' => 1,
            'amount' => 40000,
            'status' => $status,
            'code' => 'TRX-TEST-'.$status.'-'.str()->random(4),
        ], $extra));
    }

    public function test_purchase_history_requires_auth_envelope()
    {
        $this->getJson('/api/purchase-history')->assertStatus(401)->assertJsonPath('status', 'failed');
    }

    public function test_purchase_history_isolates_owner_and_maps_status()
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->makeTx($a, 'pending', [
            'fansku_support_id' => 'sup-a1',
            'fansku_raw_response' => ['payment' => ['actions' => [['descriptor' => 'QR_STRING', 'value' => 'QR123']]]],
        ]);
        $this->makeTx($a, 'approved');
        $this->makeTx($b, 'pending');

        $token = $a->createToken('mobile')->plainTextToken;

        $response = $this->getJson('/api/purchase-history', ['Authorization' => 'Bearer '.$token]);

        $response->assertOk()->assertJsonCount(2, 'data.items');
        $this->assertNotContains($b->email, array_column($response->json('data.items'), 'transaction_code'));

        $items = collect($response->json('data.items'));
        $pending = $items->firstWhere('status', 'pending');
        $paid = $items->firstWhere('status', 'paid');

        $this->assertSame('QR123', $pending['qr_string']);
        $this->assertArrayNotHasKey('qr_string', $paid);
    }

    public function test_purchase_history_rejects_invalid_query_with_422()
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile')->plainTextToken;

        $this->getJson('/api/purchase-history?perPage=200&status=bogus', [
            'Authorization' => 'Bearer '.$token,
        ])->assertStatus(422)->assertJsonPath('status', 'failed');
    }

    public function test_purchase_history_pagination_is_consistent()
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 3; $i++) {
            $this->makeTx($user, 'pending');
        }
        $token = $user->createToken('mobile')->plainTextToken;

        $this->getJson('/api/purchase-history?page=2&perPage=2', [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk()
            ->assertJsonPath('data.pagination.page', 2)
            ->assertJsonPath('data.pagination.perPage', 2)
            ->assertJsonPath('data.pagination.total', 3)
            ->assertJsonPath('data.pagination.totalPages', 2)
            ->assertJsonCount(1, 'data.items');
    }

    public function test_cover_from_alias_host_is_rewritten_to_canonical()
    {
        Http::fake([
            'komiktap.info/*' => Http::response([
                'data' => [
                    ['id' => 'a', 'title' => 'A', 'coverUrl' => 'https://komiktap.in/wp-content/a.jpg'],
                    ['id' => 'b', 'title' => 'B', 'coverUrl' => 'https://194.233.66.232/wp-content/b.png'],
                    ['id' => 'c', 'title' => 'C', 'coverUrl' => 'https://92.87.6.124/wp-content/c.webp'],
                ],
                'pagination' => ['page' => 1, 'perPage' => 20, 'total' => 3, 'totalPages' => 1],
            ], 200),
        ]);

        $response = $this->getJson('/api/v2/catalog/comics?search=abc');

        $response->assertOk();
        foreach ($response->json('data.items') as $item) {
            $this->assertStringStartsWith(url('/api/v2/catalog/image'), $item['coverUrl']);
            $this->assertStringContainsString('komiktap.info', rawurldecode($item['coverUrl']));
            $this->assertStringNotContainsString('komiktap.in/wp-content', rawurldecode($item['coverUrl']));
        }
    }

    public function test_api_user_without_accept_returns_json_401_not_redirect()
    {
        $response = $this->get('/api/user');

        $response->assertStatus(401)->assertJsonPath('status', 'failed');
    }
}
