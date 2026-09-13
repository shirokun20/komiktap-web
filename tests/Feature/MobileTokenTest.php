<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_token_is_rejected_with_401(): void
    {
        config(['sanctum.expiration' => 60]);

        $user = User::factory()->create();
        $token = $user->createToken('mobile');
        $token->accessToken->forceFill(['created_at' => now()->subMinutes(61)])->save();

        $this->getJson('/api/user', [
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->assertStatus(401);
    }

    public function test_token_expiration_has_shipped_default(): void
    {
        $this->assertSame(129600, config('sanctum.expiration'));
    }

    public function test_fresh_token_is_accepted(): void
    {
        config(['sanctum.expiration' => 60]);

        $user = User::factory()->create();
        $token = $user->createToken('mobile');

        $this->getJson('/api/user', [
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->assertOk();
    }

    public function test_logout_revokes_only_current_token(): void
    {
        $user = User::factory()->create();
        $tokenA = $user->createToken('mobile');
        $tokenB = $user->createToken('mobile');

        $this->postJson('/api/auth/logout', [], [
            'Authorization' => 'Bearer ' . $tokenA->plainTextToken,
        ])->assertOk();

        // DB-level: hanya baris token A yang terhapus.
        $this->assertNull($user->tokens()->find($tokenA->accessToken->id));
        $this->assertNotNull($user->tokens()->find($tokenB->accessToken->id));

        // HTTP-level: guard di-resolve ulang per request (AuthManager
        // meng-cache guard dalam satu test, jadi lupakan dulu).
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->getJson('/api/user', [
            'Authorization' => 'Bearer ' . $tokenA->plainTextToken,
        ])->assertStatus(401);

        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->getJson('/api/user', [
            'Authorization' => 'Bearer ' . $tokenB->plainTextToken,
        ])->assertOk();
    }

    public function test_expired_token_pruning_is_scheduled(): void
    {
        $commands = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->map(fn ($event) => $event->command ?? '')
            ->implode("\n");

        $this->assertStringContainsString('sanctum:prune-expired', $commands);
    }
}
