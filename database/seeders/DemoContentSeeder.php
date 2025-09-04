<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\Website;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $website = Website::firstOrCreate(
            ['slug' => 'blog'],
            [
                'name' => 'Demo Blog',
                'default_language' => 'en',
                'supported_languages' => ['en'],
                'is_active' => true,
            ],
        );

        // H1
        Section::updateOrCreate([
            'website_id' => $website->id,
            'slug' => 'home-h1',
            'language' => 'en',
        ], [
            'name' => 'Home H1',
            'type' => 'h1',
            'order' => 1,
            'is_published' => true,
            'components' => [
                [
                    'type' => 'heading',
                    'data' => ['level' => 'h1', 'text' => 'Welcome to the Demo Blog'],
                ],
            ],
        ]);

        // H2
        Section::updateOrCreate([
            'website_id' => $website->id,
            'slug' => 'home-h2',
            'language' => 'en',
        ], [
            'name' => 'Home H2',
            'type' => 'h2',
            'order' => 2,
            'is_published' => true,
            'components' => [
                [
                    'type' => 'heading',
                    'data' => ['level' => 'h2', 'text' => 'Latest Posts'],
                ],
                [
                    'type' => 'unfold',
                    'data' => [
                        'items' => [
                            ['title' => 'About this blog', 'content' => 'Built with Filament + Laravel JSON API.', 'open' => true],
                            ['title' => 'FAQ', 'content' => 'You can add tabs, images, and more.'],
                        ],
                    ],
                ],
                [
                    'type' => 'tabs',
                    'data' => [
                        'orientation' => 'row',
                        'tabs' => [
                            [
                                'label' => 'Intro',
                                'content' => [
                                    ['type' => 'paragraph', 'data' => ['text' => 'Welcome to the intro tab.']],
                                ],
                            ],
                            [
                                'label' => 'More',
                                'content' => [
                                    ['type' => 'heading', 'data' => ['level' => 'h3', 'text' => 'More Content']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'middlewares' => ['live-score', 'bet-boost'],
        ]);

        // Tags
        Section::updateOrCreate([
            'website_id' => $website->id,
            'slug' => 'home-tags',
            'language' => 'en',
        ], [
            'name' => 'Tags',
            'type' => 'tags',
            'order' => 3,
            'is_published' => true,
            'tags' => ['laravel', 'php', 'filament'],
        ]);

        // Content
        Section::updateOrCreate([
            'website_id' => $website->id,
            'slug' => 'home-content',
            'language' => 'en',
        ], [
            'name' => 'Home Content',
            'type' => 'content',
            'order' => 4,
            'is_published' => true,
            'components' => [
                [
                    'type' => 'heading',
                    'data' => ['level' => 'h3', 'text' => 'Getting Started'],
                ],
                [
                    'type' => 'paragraph',
                    'data' => ['text' => 'This is a demo API-driven blog powered by Filament.'],
                ],
            ],
        ]);
    }
}
