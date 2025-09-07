<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Support\WebsiteRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class WebsiteController extends Controller
{
    /**
     * GET /api/{website}/{lang}/json
     */
    public function show(string $website, string $lang): JsonResponse
    {
        // Normalize slug and language
        $slug = Str::slug($website);
        $code = self::normalizeLanguage($lang);

        $site = Website::query()->where('slug', $slug)->first();

        if (! $site) {
            return response()->json([
                'error' => 'Website not found',
            ], 404);
        }

        if (! $site->is_published) {
            return response()->json([
                'error' => 'Website is not published',
            ], 403);
        }

        if ($site->languages && ! in_array($code, $site->languages, true)) {
            return response()->json([
                'error' => 'Language not enabled for this website',
                'supported' => $site->languages,
            ], 422);
        }

        $cacheKey = "website_json:{$slug}:{$code}";
        $ttl = now()->addSeconds(30); // short TTL due to dynamic middleware feeds

        $payload = Cache::remember($cacheKey, $ttl, function () use ($site, $code) {
            return WebsiteRenderer::render($site, $code);
        });

        return response()->json($payload);
    }

    /**
     * Map human-entered names to normalized ISO-like codes.
     */
    public static function normalizeLanguage(string $raw): string
    {
        $raw = trim(strtolower($raw));
        $map = [
            'eng' => 'en', 'en' => 'en', 'english' => 'en',
            'vi' => 'vi', 'vie' => 'vi', 'vietnam' => 'vi', 'vietnamese' => 'vi',
            'thailand' => 'th', 'thai' => 'th', 'th' => 'th',
            'philippin' => 'fil', 'philippines' => 'fil', 'filipino' => 'fil', 'fil' => 'fil', 'tl' => 'fil',
            'laos' => 'lo', 'lao' => 'lo', 'lo' => 'lo',
            'id' => 'id', 'indo' => 'id', 'indonesian' => 'id',
            'ms' => 'ms', 'malay' => 'ms',
        ];

        return $map[$raw] ?? $raw;
    }
}

