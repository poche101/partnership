<?php

namespace App\Services;

use App\Models\Partner;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SemanticPartnerSearch
{
    /**
     * Returns an ordered list of partner ids matching the natural-language
     * query. Uses the Anthropic API when ANTHROPIC_API_KEY is configured,
     * otherwise falls back to a plain substring match (same fallback the
     * original app used when no AI key was present).
     */
    public function search(string $query, int $limit = 25): array
    {
        $partners = Partner::with('church')->limit(500)->get();

        $list = $partners->map(fn ($p, $i) => [
            'id' => $p->id,
            'text' => trim(sprintf(
                '%s %s %s | %s | %s | %s | KC:%s | Church:%s',
                $p->title, $p->first_name, $p->last_name,
                $p->delegate_category, $p->email, $p->phone,
                $p->kingschat_username, $p->church?->name
            )),
        ]);

        $key = config('services.anthropic.key');
        if (! $key) {
            $q = mb_strtolower($query);

            return $list->filter(fn ($p) => str_contains(mb_strtolower($p['text']), $q))
                ->pluck('id')->take($limit)->values()->all();
        }

        try {
            $prompt = "You are a search engine. From the partner list below, return ONLY a JSON array of the matching partner ids (max {$limit}), ranked by relevance to: \"{$query}\". Consider names, emails, churches, phone fragments, delegate category, KingsChat usernames, and natural language meaning. Reply with JSON array only, no prose.\n\nPARTNERS:\n"
                .$list->map(fn ($p) => "{$p['id']}: {$p['text']}")->implode("\n");

            $response = Http::withHeaders([
                'x-api-key' => $key,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model' => config('services.anthropic.model'),
                'max_tokens' => 1024,
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ]);

            if (! $response->successful()) {
                throw new \RuntimeException('AI search request failed: '.$response->status());
            }

            $text = $response->json('content.0.text', '[]');
            preg_match('/\[[\s\S]*\]/', $text, $m);
            $ids = $m ? json_decode($m[0], true) : [];

            return collect($ids)->filter(fn ($id) => is_numeric($id))->map(fn ($id) => (int) $id)->values()->all();
        } catch (\Throwable $e) {
            Log::warning('Semantic partner search failed, falling back to substring search: '.$e->getMessage());
            $q = mb_strtolower($query);

            return $list->filter(fn ($p) => str_contains(mb_strtolower($p['text']), $q))
                ->pluck('id')->take($limit)->values()->all();
        }
    }
}
