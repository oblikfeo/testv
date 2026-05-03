<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LandingTrafficService
{
    public const COUNTER_HITS = 'landing_hits';

    public const COUNTER_MODAL_OPENS = 'traffic_modal_opens';

    /**
     * Учитывает заход на главную: общий счётчик + «ведро» источника (referrer / UTM).
     */
    public function recordHomeVisit(Request $request): int
    {
        $sourceKey = $this->classifySource($request);

        return (int) DB::transaction(function () use ($sourceKey) {
            $hits = $this->bumpKeyedCounter(self::COUNTER_HITS);

            $row = DB::table('landing_source_stats')->where('source_key', $sourceKey)->lockForUpdate()->first();
            if ($row === null) {
                DB::table('landing_source_stats')->insert(['source_key' => $sourceKey, 'hits' => 1]);
            } else {
                DB::table('landing_source_stats')->where('source_key', $sourceKey)->update([
                    'hits' => (int) $row->hits + 1,
                ]);
            }

            return $hits;
        });
    }

    public function recordModalOpen(): void
    {
        DB::transaction(fn () => $this->bumpKeyedCounter(self::COUNTER_MODAL_OPENS));
    }

    /**
     * @return array{total_visits: int, modal_opens: int, sources: array<int, array{key: string, label: string, hits: int, pct: float}>}
     */
    public function publicStats(): array
    {
        $totalVisits = (int) (DB::table('site_counters')->where('key', self::COUNTER_HITS)->value('value') ?? 0);
        $modalOpens = (int) (DB::table('site_counters')->where('key', self::COUNTER_MODAL_OPENS)->value('value') ?? 0);

        $rows = DB::table('landing_source_stats')
            ->orderByDesc('hits')
            ->orderBy('source_key')
            ->limit(12)
            ->get(['source_key', 'hits']);

        $sumSources = (int) $rows->sum('hits');
        $denom = $sumSources > 0 ? $sumSources : 1;

        $sources = $rows->map(function ($row) use ($denom) {
            $key = (string) $row->source_key;
            $hits = (int) $row->hits;

            return [
                'key' => $key,
                'label' => $this->labelForSourceKey($key),
                'hits' => $hits,
                'pct' => round($hits * 100 / $denom, 1),
            ];
        })->values()->all();

        return [
            'total_visits' => $totalVisits,
            'modal_opens' => $modalOpens,
            'sources' => $sources,
        ];
    }

    public function classifySource(Request $request): string
    {
        $utm = strtolower(trim((string) $request->query('utm_source', '')));
        if ($utm !== '') {
            $slug = preg_replace('/[^a-z0-9_-]+/i', '_', $utm);
            $slug = trim((string) $slug, '_');
            if ($slug === '') {
                $slug = 'campaign';
            }

            return 'utm:'.Str::limit(strtolower($slug), 56, '');
        }

        $ref = $request->headers->get('Referer');
        if (! is_string($ref) || $ref === '') {
            return 'direct';
        }

        $host = strtolower((string) (parse_url($ref, PHP_URL_HOST) ?? ''));
        if ($host === '') {
            return 'direct';
        }

        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        $host = preg_replace('/[^a-z0-9.-]/', '', $host) ?? '';
        if ($host === '') {
            return 'direct';
        }

        return match (true) {
            str_contains($host, 'google.') => 'google',
            str_contains($host, 'yandex.') || $host === 'ya.ru' => 'yandex',
            str_contains($host, 't.me')
                || str_contains($host, 'telegram.')
                || str_contains($host, 'telegra.ph')
                || $host === 'telegram.org' => 'telegram',
            str_contains($host, 'instagram.') || str_contains($host, 'l.instagram.com') => 'instagram',
            str_contains($host, 'vk.com') || str_contains($host, 'vk.ru') || str_contains($host, 'vkontakte.ru') || str_contains($host, 'away.vk.com') => 'vk',
            str_contains($host, 'bing.') => 'bing',
            str_contains($host, 'duckduckgo.') => 'duckduckgo',
            str_contains($host, 'yahoo.') => 'yahoo',
            str_contains($host, 'youtube.') || $host === 'youtu.be' || str_contains($host, 'youtube-nocookie.') => 'youtube',
            str_contains($host, 'facebook.') || str_contains($host, 'fb.com') || str_contains($host, 'messenger.com') => 'facebook',
            str_contains($host, 'twitter.') || str_contains($host, 'x.com') || str_contains($host, 't.co') => 'twitter',
            default => 'ref:'.Str::limit($host, 100, ''),
        };
    }

    protected function labelForSourceKey(string $key): string
    {
        if (str_starts_with($key, 'utm:')) {
            $tail = substr($key, 4);

            return __('landing_traffic.label_utm', ['campaign' => $tail]);
        }

        if (str_starts_with($key, 'ref:')) {
            $tail = substr($key, 4);

            return __('landing_traffic.label_ref', ['host' => $tail]);
        }

        $map = trans('landing_traffic.sources');

        return is_array($map) && isset($map[$key]) ? $map[$key] : $key;
    }

    protected function bumpKeyedCounter(string $key): int
    {
        $row = DB::table('site_counters')->where('key', $key)->lockForUpdate()->first();
        if ($row === null) {
            DB::table('site_counters')->insert(['key' => $key, 'value' => 1]);

            return 1;
        }
        $next = (int) $row->value + 1;
        DB::table('site_counters')->where('key', $key)->update(['value' => $next]);

        return $next;
    }
}
