<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoalServeClient
{
    protected ?string $baseUrl;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.goalserve.base_url');
        $this->apiKey = config('services.goalserve.key');
    }

    /**
     * Stubbed examples; integrate with real GoalServe when keys are set.
     */
    public function liveScores(): array
    {
        if (! $this->baseUrl || ! $this->apiKey) {
            return [
                'tags' => ['LiveScore', 'Football', 'Today'],
                'data' => [
                    ['home' => 'Team A', 'away' => 'Team B', 'score' => '1-0', 'minute' => 67],
                    ['home' => 'Team C', 'away' => 'Team D', 'score' => '0-0', 'minute' => 23],
                ],
            ];
        }

        try {
            $res = Http::timeout(5)
                ->get($this->baseUrl . '/livescores', [
                    'key' => $this->apiKey,
                ]);
            if ($res->successful()) {
                $json = $res->json();
                return [
                    'tags' => ['LiveScore'],
                    'data' => $json,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('GoalServe liveScores error: ' . $e->getMessage());
        }
        return ['tags' => ['LiveScore'], 'data' => []];
    }

    public function betBoost(): array
    {
        // Placeholder feed
        return [
            'tags' => ['BetBoost'],
            'data' => [
                ['market' => 'Over 2.5', 'boost' => '+15%'],
            ],
        ];
    }

    public function gamesSlot(): array
    {
        // Placeholder feed
        return [
            'tags' => ['Games', 'Slots'],
            'data' => [
                ['title' => 'Golden Spin', 'rtp' => 96.2],
            ],
        ];
    }
}

