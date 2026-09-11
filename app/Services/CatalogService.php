<?php

namespace App\Services;

use App\Exceptions\CatalogException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CatalogService
{
    protected string $baseUrl;
    protected string $userAgent;
    protected string $referer;
    protected int $timeout;
    protected int $retryTimes;
    protected int $retrySleepMs;

    /** @var list<string> */
    protected array $imageAllowedHosts;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('catalog.upstream_base_url'), '/');
        $this->userAgent = (string) config('catalog.user_agent');
        $this->referer = (string) config('catalog.referer');
        $this->timeout = (int) config('catalog.timeout', 10);
        $this->retryTimes = (int) config('catalog.retry_times', 1);
        $this->retrySleepMs = (int) config('catalog.retry_sleep_ms', 200);
        $this->imageAllowedHosts = array_map(
            fn ($host) => mb_strtolower(trim((string) $host)),
            (array) config('catalog.image_allowed_hosts', [])
        );
    }

    /**
     * List/search comics dari upstream resmi, diteruskan apa adanya.
     *
     * @param array{search?:string,page?:int,perPage?:int,orderBy?:string,order?:string} $params
     */
    public function comics(array $params): array
    {
        $query = [
            'search' => (string) ($params['search'] ?? ''),
            'page' => max(1, (int) ($params['page'] ?? 1)),
            'perPage' => (int) ($params['perPage'] ?? 20),
            'orderBy' => (string) ($params['orderBy'] ?? 'lastUpdated'),
            'order' => (string) ($params['order'] ?? 'desc'),
        ];

        $payload = $this->remember('list', ['comics', $query], (int) config('catalog.ttl.list', 600), function () use ($query) {
            return $this->get('/comics', $query);
        });

        if (! is_array($payload) || ! isset($payload['data']) || ! is_array($payload['data'])) {
            throw new CatalogException('Respons katalog tidak valid.', 502);
        }

        $payload['data'] = array_values(array_map(function ($item) {
            if (is_array($item) && isset($item['coverUrl'])) {
                $item['coverUrl'] = $this->imageUrl((string) $item['coverUrl']);
            }

            return $item;
        }, $payload['data']));

        return [
            'items' => $payload['data'],
            'pagination' => $this->normalizePagination($payload['pagination'] ?? [], $query),
        ];
    }

    /**
     * Komik per genre (content-by-tag) via search upstream.
     * Endpoint khusus agar tidak mengganggu search umum: slug
     * "martial-arts" dipetakan menjadi search "Martial Arts".
     *
     * @param array{page?:int,perPage?:int,orderBy?:string,order?:string} $params
     */
    public function byGenre(string $slug, array $params): array
    {
        $genre = trim(str_replace('-', ' ', $slug));

        if ($genre === '') {
            throw new CatalogException('Genre tidak valid.', 422);
        }

        $result = $this->comics([
            'search' => $genre,
            'page' => $params['page'] ?? 1,
            'perPage' => $params['perPage'] ?? 20,
            'orderBy' => $params['orderBy'] ?? 'lastUpdated',
            'order' => $params['order'] ?? 'desc',
        ]);

        $result['genre'] = $genre;

        return $result;
    }

    /**
     * Detail komik + daftar chapter (tanpa URL gambar halaman).
     */
    public function detail(string $id): array
    {
        $detail = $this->remember('detail', ['comics', $id], (int) config('catalog.ttl.detail', 3600), function () use ($id) {
            return $this->get('/comics/'.rawurlencode($id));
        });

        if (! is_array($detail) || ! isset($detail['id'])) {
            throw new CatalogException('Respons katalog tidak valid.', 502);
        }

        if (isset($detail['coverUrl'])) {
            $detail['coverUrl'] = $this->imageUrl((string) $detail['coverUrl']);
        }

        if (isset($detail['chapters']) && is_array($detail['chapters'])) {
            $detail['chapters'] = array_values(array_map(function ($chapter) {
                if (! is_array($chapter)) {
                    return $chapter;
                }
                unset($chapter['pages']);

                return $chapter;
            }, $detail['chapters']));
        }

        return $detail;
    }

    /**
     * Halaman gambar satu chapter (URL ditulis ulang ke proxy lokal).
     */
    public function chapter(string $id, string $number): array
    {
        $chapter = $this->remember('detail', ['chapter', $id, $number], (int) config('catalog.ttl.detail', 3600), function () use ($id, $number) {
            return $this->get('/comics/'.rawurlencode($id).'/chapters/'.rawurlencode($number));
        });

        if (! is_array($chapter) || ! isset($chapter['pages']) || ! is_array($chapter['pages'])) {
            throw new CatalogException('Respons katalog tidak valid.', 502);
        }

        $chapter['pages'] = array_values(array_map(
            fn ($url) => $this->imageUrl((string) $url),
            $chapter['pages']
        ));

        return $chapter;
    }

    /**
     * Daftar pengumuman katalog.
     */
    public function announcements(): array
    {
        $payload = $this->remember('announcements', ['announcements'], (int) config('catalog.ttl.announcements', 900), function () {
            return $this->get('/announcements');
        });

        return is_array($payload) ? array_values($payload) : [];
    }

    /**
     * Ambil HTML halaman situs (genre index, A-Z list) dengan cache.
     *
     * @throws CatalogException
     */
    public function fetchSiteHtml(string $path): string
    {
        $ttl = (int) config('catalog.ttl.genres', 86400);

        return (string) $this->remember('site-html', [$path], $ttl, function () use ($path) {
            $url = rtrim($this->siteBaseUrl(), '/').'/'.ltrim($path, '/');

            try {
                $response = Http::withHeaders([
                    'Accept' => 'text/html,application/xhtml+xml',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Referer' => $this->referer,
                    'User-Agent' => $this->userAgent,
                ])
                    ->timeout($this->timeout)
                    ->retry($this->retryTimes, $this->retrySleepMs)
                    ->get($url);
            } catch (ConnectionException $e) {
                Log::warning('CatalogService: site html timeout', ['url' => $url]);
                throw new CatalogException('Katalog tidak tersedia sementara.', 504);
            }

            if ($response->status() === 404) {
                throw new CatalogException('Katalog tidak ditemukan.', 404);
            }

            if (! $response->successful()) {
                Log::warning('CatalogService: site html error', ['url' => $url, 'status' => $response->status()]);
                throw new CatalogException('Katalog tidak tersedia sementara.', 502);
            }

            $body = (string) $response->body();

            if ($body === '') {
                throw new CatalogException('Respons katalog tidak valid.', 502);
            }

            return $body;
        });
    }

    protected function siteBaseUrl(): string
    {
        $configured = trim((string) config('catalog.site_base_url', ''));

        if ($configured !== '') {
            return rtrim($configured, '/');
        }

        // Turunkan dari referer (https://komiktap.info/) bila tidak dikonfigurasi.
        $fromReferer = $this->referer !== '' ? (string) parse_url($this->referer, PHP_URL_SCHEME).'://'.(string) parse_url($this->referer, PHP_URL_HOST) : '';

        return $fromReferer !== '://' && $fromReferer !== '' ? $fromReferer : 'https://komiktap.info';
    }

    /**
     * Tulis ulang URL gambar upstream menjadi endpoint proxy lokal.
     * URL di luar allowlist dikembalikan apa adanya.
     */
    public function imageUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '' || ! str_starts_with($url, 'http')) {
            return $url;
        }

        $host = mb_strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($host === '' || ! in_array($host, $this->imageAllowedHosts, true)) {
            return $url;
        }

        return url('/api/v2/catalog/image').'?u='.rawurlencode($url);
    }

    /**
     * Ambil bytes gambar upstream untuk diproxy.
     *
     * @return array{body:string,content_type:string}
     *
     * @throws CatalogException
     */
    public function fetchImage(string $url): array
    {
        $url = trim($url);

        if ($url === '' || ! str_starts_with($url, 'http')) {
            throw new CatalogException('URL gambar tidak valid.', 422);
        }

        $host = mb_strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($host === '' || ! in_array($host, $this->imageAllowedHosts, true)) {
            throw new CatalogException('Host gambar tidak diizinkan.', 403);
        }

        $maxBytes = max(1024, (int) config('catalog.image_max_bytes', 15728640));
        $timeout = max(5, (int) config('catalog.image_timeout', 15));

        try {
            $response = Http::withHeaders([
                'Referer' => $this->referer,
                'User-Agent' => $this->userAgent,
            ])
                ->timeout($timeout)
                ->retry($this->retryTimes, $this->retrySleepMs)
                ->get($url);
        } catch (ConnectionException $e) {
            Log::warning('CatalogService: image timeout', ['url' => $url]);
            throw new CatalogException('Gambar tidak tersedia sementara.', 504);
        }

        if ($response->status() === 404) {
            throw new CatalogException('Gambar tidak ditemukan.', 404);
        }

        if (! $response->successful()) {
            Log::warning('CatalogService: image error', ['url' => $url, 'status' => $response->status()]);
            throw new CatalogException('Gambar tidak tersedia sementara.', 502);
        }

        $contentType = mb_strtolower((string) $response->header('Content-Type'));

        if (! str_starts_with($contentType, 'image/')) {
            throw new CatalogException('Respons bukan gambar.', 502);
        }

        $body = (string) $response->body();

        if ($body === '' || strlen($body) > $maxBytes) {
            throw new CatalogException('Ukuran gambar tidak valid.', 502);
        }

        return ['body' => $body, 'content_type' => explode(';', $contentType)[0]];
    }

    /**
     * GET ke upstream dengan retry + mapping error konsisten.
     *
     * @param array<string,mixed> $query
     * @return mixed
     *
     * @throws CatalogException
     */
    protected function get(string $path, array $query = []): mixed
    {
        $url = $this->baseUrl.$path;

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Referer' => $this->referer,
                'User-Agent' => $this->userAgent,
            ])
                ->timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleepMs)
                ->get($url, $query);
        } catch (ConnectionException $e) {
            Log::warning('CatalogService: upstream timeout', ['url' => $url]);
            throw new CatalogException('Katalog tidak tersedia sementara.', 504);
        }

        $status = $response->status();
        $contentType = (string) $response->header('Content-Type');
        $body = (string) $response->body();

        if ($this->looksLikeHtml($contentType, $body)) {
            if ($status === 404) {
                throw new CatalogException('Katalog tidak ditemukan.', 404);
            }

            Log::warning('CatalogService: upstream HTML error', ['url' => $url, 'status' => $status]);
            throw new CatalogException('Katalog tidak tersedia sementara.', 502);
        }

        if ($status === 404) {
            throw new CatalogException('Katalog tidak ditemukan.', 404);
        }

        if ($status === 401 || $status === 403) {
            throw new CatalogException('Katalog tidak tersedia sementara.', 502);
        }

        if (! $response->successful()) {
            Log::warning('CatalogService: upstream error', ['url' => $url, 'status' => $status]);
            throw new CatalogException('Katalog tidak tersedia sementara.', 502);
        }

        $json = $response->json();

        if ($json === null && trim($body) !== '' && trim($body) !== 'null') {
            throw new CatalogException('Respons katalog tidak valid.', 502);
        }

        return $json;
    }

    /**
     * @param callable():mixed $callback
     * @return mixed
     */
    protected function remember(string $group, array $parts, int $ttl, callable $callback): mixed
    {
        $key = 'catalog:v2:'.$group.':'.sha1(json_encode($parts) ?: '');

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * @param array<string,mixed> $query
     * @return array{page:int,perPage:int,total:int,totalPages:int}
     */
    protected function normalizePagination(array $pagination, array $query): array
    {
        return [
            'page' => (int) ($pagination['page'] ?? $query['page']),
            'perPage' => (int) ($pagination['perPage'] ?? $query['perPage']),
            'total' => (int) ($pagination['total'] ?? 0),
            'totalPages' => (int) ($pagination['totalPages'] ?? 0),
        ];
    }

    protected function looksLikeHtml(string $contentType, string $body): bool
    {
        if (str_contains(mb_strtolower($contentType), 'text/html')) {
            return true;
        }

        $trimmed = ltrim(mb_strtolower($body));

        return str_starts_with($trimmed, '<!doctype html')
            || str_starts_with($trimmed, '<html');
    }
}
