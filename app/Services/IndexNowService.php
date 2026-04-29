<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Клиент IndexNow — мгновенно сообщает поисковикам (Яндекс, Bing, Naver, Seznam)
 * об обновлении страниц, чтобы индексация занимала минуты, а не недели.
 *
 * Документация:
 *  - https://yandex.ru/support/webmaster/indexnow/
 *  - https://www.indexnow.org/documentation
 *
 * Подтверждение владения сайтом происходит по файлу /<key>.txt — обслуживается
 * роутом приложения (см. routes/web.php).
 */
class IndexNowService
{
    /**
     * Возвращает действующий ключ IndexNow.
     * Если в .env не задан INDEXNOW_KEY — генерируется детерминированно из APP_KEY,
     * чтобы значение оставалось стабильным между деплоями без ручной настройки.
     */
    public function key(): string
    {
        $explicit = (string) config('services.indexnow.key', '');
        if ($explicit !== '') {
            return $this->normalizeKey($explicit);
        }

        $appKey = (string) config('app.key', '');
        $base = $appKey !== '' ? hash('sha256', $appKey . '|indexnow') : str_repeat('0', 64);

        return substr($base, 0, 32);
    }

    /**
     * Базовый URL сайта без слэша на конце.
     */
    public function baseUrl(): string
    {
        return rtrim((string) config('app.url'), '/');
    }

    /**
     * Хост (домен) — нужен поисковикам в payload.
     */
    public function host(): string
    {
        return (string) parse_url($this->baseUrl(), PHP_URL_HOST);
    }

    /**
     * Публичный URL файла подтверждения ключа.
     */
    public function keyLocation(): string
    {
        return $this->baseUrl() . '/' . $this->key() . '.txt';
    }

    /**
     * Отправить список URL в IndexNow. Возвращает true при успехе (HTTP 200/202).
     *
     * @param  array<int, string>  $urls
     */
    public function submit(array $urls): bool
    {
        $urls = array_values(array_unique(array_filter(array_map('strval', $urls))));

        if ($urls === []) {
            return false;
        }

        $endpoint = (string) config('services.indexnow.endpoint', 'https://api.indexnow.org/indexnow');
        $payload = [
            'host' => $this->host(),
            'key' => $this->key(),
            'keyLocation' => $this->keyLocation(),
            'urlList' => $urls,
        ];

        try {
            $response = Http::asJson()
                ->acceptJson()
                ->timeout(10)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                Log::info('IndexNow: submitted', [
                    'count' => count($urls),
                    'status' => $response->status(),
                ]);
                return true;
            }

            Log::warning('IndexNow: submit failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'urls' => $urls,
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::warning('IndexNow: exception', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * IndexNow допускает только символы [a-zA-Z0-9-] длиной 8–128.
     */
    protected function normalizeKey(string $key): string
    {
        $key = preg_replace('/[^a-zA-Z0-9\-]/', '', $key) ?? '';
        if (strlen($key) < 8) {
            $key = str_pad($key, 8, '0');
        }
        if (strlen($key) > 128) {
            $key = substr($key, 0, 128);
        }
        return $key;
    }
}
