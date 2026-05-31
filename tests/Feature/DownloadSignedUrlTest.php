<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class DownloadSignedUrlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('download');
    }

    private function makeApk(array $overrides = []): \App\Models\ApkVersion
    {
        return \App\Models\ApkVersion::create(array_merge([
            'version_name'   => '1.0.0',
            'version_code'   => '100',
            'file_path'      => 'apk/test.apk',
            'is_active'      => true,
            'download_count' => 0,
        ], $overrides));
    }

    private function signedUrl(string $versionCode, int $expires = null): string
    {
        $expires   = $expires ?? now()->addMinutes(30)->timestamp;
        $signature = hash_hmac('sha256', "download:{$versionCode}:{$expires}", config('app.key'));

        return "/download/{$versionCode}?expires={$expires}&signature={$signature}";
    }

    // -------------------------------------------------------
    // 9.13 Valid signed URL — serves file (or 404 if file missing on disk)
    // -------------------------------------------------------

    public function test_valid_signed_url_passes_validation(): void
    {
        $apk = $this->makeApk();
        $url = $this->signedUrl($apk->version_code);

        // File won't exist on disk in test env, but we should NOT get 403/410/redirect
        $response = $this->get($url);
        $this->assertNotEquals(403, $response->status());
        $this->assertNotEquals(410, $response->status());
        $this->assertNotEquals(302, $response->status()); // no redirect to download page
    }

    // -------------------------------------------------------
    // 9.13 Expired signed URL → 410
    // -------------------------------------------------------

    public function test_expired_signed_url_returns_410(): void
    {
        $apk     = $this->makeApk();
        $expires = now()->subMinutes(5)->timestamp; // already expired
        $url     = $this->signedUrl($apk->version_code, $expires);

        $this->get($url)->assertStatus(410);
    }

    // -------------------------------------------------------
    // 9.13 Invalid signature → 403
    // -------------------------------------------------------

    public function test_invalid_signature_returns_403(): void
    {
        $apk     = $this->makeApk();
        $expires = now()->addMinutes(30)->timestamp;
        $url     = "/download/{$apk->version_code}?expires={$expires}&signature=bad-signature";

        $this->get($url)->assertStatus(403);
    }

    // -------------------------------------------------------
    // 9.13 Missing signature params → redirect to download page
    // -------------------------------------------------------

    public function test_missing_signature_params_redirects_to_download_page(): void
    {
        $apk = $this->makeApk();

        $this->get("/download/{$apk->version_code}")
             ->assertRedirect(route('download.index'));
    }

    // -------------------------------------------------------
    // Ticket route generates valid signed URL redirect
    // -------------------------------------------------------

    public function test_ticket_route_redirects_to_signed_url(): void
    {
        $apk = $this->makeApk();

        $response = $this->get(route('download.ticket', $apk->version_code));

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('expires=', $location);
        $this->assertStringContainsString('signature=', $location);
    }
}
