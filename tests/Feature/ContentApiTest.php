<?php

namespace Tests\Feature;

use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_website_json(): void
    {
        $this->seed(DemoContentSeeder::class);

        $response = $this->getJson('/api/blog/en/json');

        $response->assertOk();
        $response->assertJsonStructure([
            'website' => ['name', 'slug', 'default_language'],
            'language',
            'sections',
        ]);
    }
}

