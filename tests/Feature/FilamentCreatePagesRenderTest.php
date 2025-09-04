<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentCreatePagesRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_sections_create_page_renders(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/admin/sections/create');
        $response->assertStatus(200);
    }

    public function test_websites_create_page_renders(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/admin/websites/create');
        $response->assertStatus(200);
    }
}

