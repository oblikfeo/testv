<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LandingTrafficService
{
    public const COUNTER_HITS = 'landing_hits';

    public const COUNTER_MODAL_OPENS = 'traffic_modal_opens';

    /**
     * Хосты поисковых систем, чьи органические переходы нас интересуют.
     * Всё остальное (прямые заходы, соцсети, мессенджеры, боты) не считаем.
     */
    private const SEARCH_ENGINE_HOSTS = [
        'google' => ['google.'],
        'yandex' => ['yandex.', 'ya.ru'],
        'bing' => ['bing.'],
        'duckduckgo' => ['duckduckgo.'],
        'yahoo' => ['yahoo.'],
    ];

    /**
     * Признаки бота/краулера/скрипта в User-Agent — такие визиты не считаем,
     * даже если у них почему-то оказался поисковый referrer.
     */
    private const BOT_UA_PATTERNS = [
        'bot', 'crawl', 'spider', 'slurp', 'mediapartners', 'inspectiontool',
        'adsbot', 'apis-google', 'feedfetcher', 'duplexweb', 'bingpreview',
        'headlesschrome', 'phantomjs', 'python-requests', 'python-urllib',
        'curl/', 'wget/', 'go-http-client', 'okhttp', 'node-fetch', 'axios/',
        'scrapy', 'httpclient', 'libwww-perl', 'semrush', 'ahrefs', 'mj12',
        'dotbot', 'petalbot', 'bytespider', 'gptbot', 'ccbot', 'claudebot',
        'perplexity', 'archive.org', 'ia_archiver', 'facebookexternalhit',
        'telegrambot', 'whatsapp', 'headless',
    ];

    /**
     * Учитывает заход на главную ТОЛЬКО если это живой человек, пришедший
     * из органической выдачи поисковика. Возвращает текущее значение
     * счётчика в любом случае (для отображения), но инкрементирует его
     * только для квалифицированных визитов.
     */
    public function recordHomeVisit(Request $request): int
    {
        $searchEngine = $this->isBot($request) ? null : $this->classifySearchEngine($request);

        if ($searchEngine === null) {
            return (int) (DB::table('site_counters')->where('key', self::COUNTER_HITS)->value('value') ?? 0);
        }

        return (int) DB::transaction(function () use ($searchEngine) {
            $hits = $this->bumpKeyedCounter(self::COUNTER_HITS);

            $row = DB::table('landing_source_stats')->where('source_key', $searchEngine)->lockForUpdate()->first();
            if ($row === null) {
                DB::table('landing_source_stats')->insert(['source_key' => $searchEngine, 'hits' => 1]);
            } else {
                DB::table('landing_source_stats')->where('source_key', $searchEngine)->update([
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
            ->get(['source_key', 'hits']);

        $sumSources = (int) $rows->sum('hits');
        $denom = $sumSources > 0 ? $sumSources : 1;

        $sources = $rows->map(fn ($row) => [
            'key' => (string) $row->source_key,
            'label' => $this->labelForSourceKey((string) $row->source_key),
            'hits' => (int) $row->hits,
            'pct' => round((int) $row->hits * 100 / $denom, 1),
        ])->values()->all();

        return [
            'total_visits' => $totalVisits,
            'modal_opens' => $modalOpens,
            'sources' => $sources,
        ];
    }

    /**
     * Возвращает ключ поисковика (google/yandex/bing/duckduckgo/yahoo),
     * если Referer указывает на него, иначе null (прямой заход, соцсеть,
     * мессенджер и т.д. — нас не интересует).
     */
    public function classifySearchEngine(Request $request): ?string
    {
        $ref = $request->headers->get('Referer');
        if (! is_string($ref) || $ref === '') {
            return null;
        }

        $host = strtolower((string) (parse_url($ref, PHP_URL_HOST) ?? ''));
        if ($host === '') {
            return null;
        }

        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        foreach (self::SEARCH_ENGINE_HOSTS as $key => $needles) {
            foreach ($needles as $needle) {
                if ($host === $needle || str_contains($host, $needle)) {
                    return $key;
                }
            }
        }

        return null;
    }

    public function isBot(Request $request): bool
    {
        $ua = strtolower((string) $request->headers->get('User-Agent', ''));
        if ($ua === '') {
            return true;
        }

        foreach (self::BOT_UA_PATTERNS as $pattern) {
            if (str_contains($ua, $pattern)) {
                return true;
            }
        }

        return false;
    }

    protected function labelForSourceKey(string $key): string
    {
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
