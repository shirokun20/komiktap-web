<?php

namespace Tests\Feature;

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Tests\TestCase;

class TrustedProxyTest extends TestCase
{
    public function test_default_trusted_proxies_include_cloudflare_ranges(): void
    {
        $proxies = (array) config('trustedproxy.proxies');

        $this->assertContains('173.245.48.0/20', $proxies);
        $this->assertContains('104.16.0.0/13', $proxies);
        $this->assertContains('2400:cb00::/32', $proxies);
    }

    public function test_client_ip_resolved_from_forwarded_header_when_peer_trusted(): void
    {
        config(['trustedproxy.proxies' => '173.245.48.0/20']);

        $request = Request::create('/', 'GET', [], [], [], [
            'REMOTE_ADDR' => '173.245.48.10',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.7',
        ]);

        (new TrustProxies)->handle($request, function ($req) {
            $this->assertSame('203.0.113.7', $req->ip());

            return response('ok');
        });
    }

    public function test_forwarded_header_ignored_when_peer_untrusted(): void
    {
        config(['trustedproxy.proxies' => '173.245.48.0/20']);

        $request = Request::create('/', 'GET', [], [], [], [
            'REMOTE_ADDR' => '198.51.100.9',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.7',
        ]);

        (new TrustProxies)->handle($request, function ($req) {
            $this->assertSame('198.51.100.9', $req->ip());

            return response('ok');
        });
    }
}
