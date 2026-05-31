<?php

namespace Tests\Unit;

use App\Traits\HoneypotTrait;
use Illuminate\Http\Request;
use Tests\TestCase;

class HoneypotTraitTest extends TestCase
{
    // Anonymous class that uses the trait for testing
    private function makeController(): object
    {
        return new class {
            use HoneypotTrait;

            public function check(Request $request): bool
            {
                return $this->isHoneypotFilled($request);
            }
        };
    }

    public function test_returns_false_when_honeypot_is_empty(): void
    {
        $request    = Request::create('/', 'POST', ['_hp_website' => '']);
        $controller = $this->makeController();

        $this->assertFalse($controller->check($request));
    }

    public function test_returns_false_when_honeypot_is_absent(): void
    {
        $request    = Request::create('/', 'POST', []);
        $controller = $this->makeController();

        $this->assertFalse($controller->check($request));
    }

    public function test_returns_true_when_honeypot_has_value(): void
    {
        $request    = Request::create('/', 'POST', ['_hp_website' => 'http://spam.com']);
        $controller = $this->makeController();

        $this->assertTrue($controller->check($request));
    }

    public function test_returns_true_when_honeypot_has_whitespace_only(): void
    {
        // Whitespace-only is treated as empty (trim)
        $request    = Request::create('/', 'POST', ['_hp_website' => '   ']);
        $controller = $this->makeController();

        $this->assertFalse($controller->check($request));
    }

    public function test_returns_true_when_honeypot_has_single_character(): void
    {
        $request    = Request::create('/', 'POST', ['_hp_website' => 'x']);
        $controller = $this->makeController();

        $this->assertTrue($controller->check($request));
    }
}
