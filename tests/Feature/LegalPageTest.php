<?php

namespace Tests\Feature;

use Database\Seeders\LegalPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(LegalPageSeeder::class);
    }

    public function test_privacy_policy_page_is_publicly_accessible(): void
    {
        $this->get('/privacy-policy')
            ->assertOk()
            ->assertSee('Kebijakan Privasi', false)
            ->assertSee('Google', false);
    }

    public function test_terms_page_is_publicly_accessible(): void
    {
        $this->get('/terms')
            ->assertOk()
            ->assertSee('Syarat dan Ketentuan Layanan', false);
    }

    public function test_homepage_links_to_legal_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('/privacy-policy', false)
            ->assertSee('/terms', false);
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(LegalPageSeeder::class);

        $this->assertDatabaseCount('pages', 2);
    }
}
