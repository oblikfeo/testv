<?php

namespace App\Console\Commands;

use App\Services\IndexNowService;
use Illuminate\Console\Command;

class IndexNowSubmitCommand extends Command
{
    /**
     * Использование:
     *   php artisan indexnow:submit                       # отправить главную и ключевые страницы
     *   php artisan indexnow:submit https://avavpn.ru/    # отправить указанные URL
     *   php artisan indexnow:submit /privacy /offer       # относительные пути дополняются APP_URL
     */
    protected $signature = 'indexnow:submit {urls?*}';

    protected $description = 'Отправить URL в IndexNow (Яндекс/Bing/Naver) для мгновенной индексации';

    public function handle(IndexNowService $indexNow): int
    {
        $base = $indexNow->baseUrl();
        if ($base === '') {
            $this->error('APP_URL не задан в .env — нечего отправлять.');
            return self::FAILURE;
        }

        $raw = (array) $this->argument('urls');

        if ($raw === []) {
            $raw = [
                '/',
                '/#features',
                '/#how',
                '/#pricing',
                '/#faq',
                '/privacy',
                '/offer',
                '/personal-data',
            ];
        }

        $urls = array_map(function ($url) use ($base) {
            $url = trim((string) $url);
            if ($url === '') return '';
            if (preg_match('#^https?://#i', $url)) return $url;
            if (! str_starts_with($url, '/')) $url = '/' . $url;
            return $base . $url;
        }, $raw);

        $urls = array_values(array_filter($urls));

        $this->info('Ключ: ' . $indexNow->key());
        $this->info('keyLocation: ' . $indexNow->keyLocation());
        $this->line('URL для отправки:');
        foreach ($urls as $u) {
            $this->line('  • ' . $u);
        }

        $ok = $indexNow->submit($urls);

        if ($ok) {
            $this->info('IndexNow: отправлено успешно (' . count($urls) . ' URL).');
            return self::SUCCESS;
        }

        $this->error('IndexNow: ошибка отправки. Подробности в logs/laravel.log.');
        return self::FAILURE;
    }
}
