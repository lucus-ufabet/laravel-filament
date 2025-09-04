<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SiteContentController extends Controller
{
    public function show(string $websiteSlug, string $language): JsonResponse
    {
        $website = Website::query()
            ->where('slug', $websiteSlug)
            ->where('is_active', true)
            ->first();

        if (! $website) {
            return response()->json(['message' => 'Website not found'], 404);
        }

        $sections = $website->sections()
            ->where('language', $language)
            ->where('is_published', true)
            ->orderBy('order')
            ->get()
            ->map(function ($section) {
                $data = [
                    'id' => $section->id,
                    'type' => $section->type,
                    'slug' => $section->slug,
                    'name' => $section->name,
                    'order' => $section->order,
                    'user_selectable' => (bool) $section->user_selectable,
                    'audiences' => array_values($section->audiences ?? []),
                    'middlewares' => array_values($section->middlewares ?? []),
                ];

                if ($section->type === 'tags') {
                    $data['tags'] = array_values($section->tags ?? []);
                } else {
                    $data['components'] = $section->components ?? [];
                }

                return $data;
            })
            ->values();

        $result = [
            'website' => [
                'name' => $website->name,
                'slug' => $website->slug,
                'default_language' => $website->default_language,
                'supported_languages' => $website->supported_languages,
                'logo_url' => $website->logo_path ? Storage::disk('public')->url($website->logo_path) : null,
                'favicon_url' => $website->favicon_path ? Storage::disk('public')->url($website->favicon_path) : null,
                'robots' => $website->robots_txt,
                'google_tag' => $website->google_tag,
                'manifest' => $website->site_manifest,
            ],
            'language' => $language,
            'sections' => $sections,
        ];

        return response()->json($result);
    }
}
