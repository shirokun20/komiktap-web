<?php

namespace App\Services;

use App\Exceptions\CatalogException;
use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Parser halaman HTML upstream (genre index + A-Z list).
 * Dipakai karena API resmi tidak menyediakan endpoint genre/alfabet.
 */
class CatalogHtmlService
{
    public function __construct(protected CatalogService $catalog) {}

    /** @var array<string,string> */
    public const TYPE_PATHS = [
        'manga' => '/list-manga/',
        'manhua' => '/list-manhua/',
        'manhwa' => '/list-manhwa/',
    ];

    /** @var array<string,string> */
    public const STATUS_PATHS = [
        'ongoing' => '/ongoing/',
        'completed' => '/tamat/',
    ];

    public const PROJECTS_PATH = '/project/';

    /**
     * Daftar terfilter (tipe/status) dari halaman list situs.
     * Upstream resmi tidak mendukung filter ini, jadi sumbernya scrape
     * server-side halaman yang memang sudah terfilter natively.
     * Paginasi 1:1 dengan halaman situs (page klienta = page situs);
     * `total` = totalPages situs x jumlah card halaman ini (aproksimasi,
     * cukup untuk logika hasMore; totalPages-nya eksak dari nav situs).
     *
     * @return array{items:list<array{id:string,title:string,coverUrl:string,rating:float|null,status:string|null,type:string|null,totalChapters:int|null}>,pagination:array{page:int,perPage:int,total:int,totalPages:int}}
     */
    public function filteredList(?string $type, ?string $status, string $search = '', int $page = 1, int $perPage = 20, string $orderBy = 'lastUpdated', string $order = 'desc'): array
    {
        $page = max(1, $page);
        $base = $type !== null && isset(self::TYPE_PATHS[$type])
            ? self::TYPE_PATHS[$type]
            : (isset(self::STATUS_PATHS[(string) $status]) ? self::STATUS_PATHS[(string) $status] : null);

        if ($base === null) {
            throw new CatalogException('Filter tidak valid.', 400);
        }

        $path = $page <= 1 ? $base : rtrim($base, '/').'/page/'.$page.'/';

        try {
            $html = $this->catalog->fetchSiteHtml($path);
        } catch (CatalogException $e) {
            // Halaman di luar jangkauan situs = hasil kosong, bukan error.
            if ($e->status() === 404 && $page > 1) {
                return ['items' => [], 'pagination' => ['page' => $page, 'perPage' => $perPage, 'total' => 0, 'totalPages' => 0]];
            }

            throw $e;
        }

        $doc = $this->loadHtml($html);
        $xpath = new DOMXPath($doc);

        $items = $this->listCards($xpath);

        // Saring lokal: status (bila sumbernya halaman tipe) + search.
        if ($status !== null && $status !== '') {
            $want = mb_strtolower($status) === 'complete' ? 'completed' : mb_strtolower($status);
            $items = array_values(array_filter(
                $items,
                fn ($it) => mb_strtolower((string) ($it['status'] ?? '')) === $want
            ));
        }

        if (trim($search) !== '') {
            $needle = mb_strtolower(trim($search));
            $items = array_values(array_filter(
                $items,
                fn ($it) => str_contains(mb_strtolower((string) $it['title']), $needle)
            ));
        }

        $items = $this->sortCards($items, $orderBy, $order);

        $nav = $this->azPagination($xpath, $page);
        $totalPages = max($page, $nav['totalPages']);
        $count = count($items);

        if ($count === 0) {
            \Illuminate\Support\Facades\Log::warning('CatalogHtmlService: zero cards parsed', ['path' => $path]);

            return ['items' => [], 'pagination' => ['page' => $page, 'perPage' => $perPage, 'total' => 0, 'totalPages' => 0]];
        }

        return [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $totalPages * $count,
                'totalPages' => $totalPages,
            ],
        ];
    }

    /**
     * Daftar Project dari /project/ (+ /page/{n}/), envelope sama.
     *
     * @return array{items:list<array{id:string,title:string,coverUrl:string,rating:float|null,status:string|null,type:string|null,totalChapters:int|null}>,pagination:array{page:int,perPage:int,total:int,totalPages:int}}
     */
    public function projectsList(int $page = 1, int $perPage = 20, string $orderBy = 'lastUpdated', string $order = 'desc'): array
    {
        $page = max(1, $page);
        $path = $page <= 1 ? self::PROJECTS_PATH : rtrim(self::PROJECTS_PATH, '/').'/page/'.$page.'/';

        try {
            $html = $this->catalog->fetchSiteHtml($path);
        } catch (CatalogException $e) {
            if ($e->status() === 404 && $page > 1) {
                return ['items' => [], 'pagination' => ['page' => $page, 'perPage' => $perPage, 'total' => 0, 'totalPages' => 0]];
            }

            throw $e;
        }

        $doc = $this->loadHtml($html);
        $xpath = new DOMXPath($doc);

        $items = $this->sortCards($this->listCards($xpath), $orderBy, $order);
        $nav = $this->azPagination($xpath, $page);
        $totalPages = max($page, $nav['totalPages']);
        $count = count($items);

        if ($count === 0) {
            \Illuminate\Support\Facades\Log::warning('CatalogHtmlService: zero project cards parsed', ['path' => $path]);

            return ['items' => [], 'pagination' => ['page' => $page, 'perPage' => $perPage, 'total' => 0, 'totalPages' => 0]];
        }

        return [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $totalPages * $count,
                'totalPages' => $totalPages,
            ],
        ];
    }

    /**
     * @return list<array{id:string,title:string,coverUrl:string,rating:float|null,status:string|null,type:string|null,totalChapters:int|null}>
     */
    protected function listCards(DOMXPath $xpath): array
    {
        $items = [];

        $nodes = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " bs ")]');
        if ($nodes === false) {
            return $items;
        }

        foreach ($nodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $item = $this->listCard($node, $xpath);
            if ($item !== null) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @return array{id:string,title:string,coverUrl:string,rating:float|null,status:string|null,type:string|null,totalChapters:int|null}|null
     */
    protected function listCard(DOMElement $node, DOMXPath $xpath): ?array
    {
        $link = $xpath->query('.//a[@href]', $node)->item(0);
        if (! $link instanceof DOMElement) {
            return null;
        }

        $title = trim($link->getAttribute('title'));
        if ($title === '') {
            $title = trim((string) $xpath->evaluate('string(.//div[contains(concat(" ", normalize-space(@class), " "), " tt ")])', $node));
        }

        $slug = $this->mangaSlugFromUrl(trim($link->getAttribute('href')));
        if ($slug === null || $title === '') {
            return null;
        }

        $img = $xpath->query('.//img[@src]', $node)->item(0);
        $cover = $img instanceof DOMElement ? trim($img->getAttribute('src')) : '';

        $status = $xpath->evaluate('string(.//span[contains(concat(" ", normalize-space(@class), " "), " status ")])', $node);
        // <span class="type Manhwa"></span>: nama tipe ada di atribut class, bukan teks.
        $typeNode = $xpath->query('.//span[contains(concat(" ", normalize-space(@class), " "), " type ")]', $node)->item(0);
        $type = null;
        if ($typeNode instanceof DOMElement) {
            foreach (preg_split('/\s+/', trim($typeNode->getAttribute('class'))) ?: [] as $token) {
                if (strcasecmp($token, 'type') !== 0 && $token !== '') {
                    $type = $token;
                    break;
                }
            }
            if ($type === null) {
                $text = trim($typeNode->textContent);
                $type = $text !== '' ? $text : null;
            }
        }
        $score = $xpath->evaluate('string(.//div[contains(concat(" ", normalize-space(@class), " "), " numscore ")])', $node);
        $ep = $xpath->evaluate('string(.//div[contains(concat(" ", normalize-space(@class), " "), " epxs ")])', $node);

        $chapters = null;
        if (is_string($ep) && preg_match('/(\d+)/', $ep, $m)) {
            $chapters = (int) $m[1];
        }

        return [
            'id' => $slug,
            'title' => html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'coverUrl' => $this->catalog->imageUrl($cover),
            'rating' => is_string($score) && is_numeric(trim($score)) ? (float) trim($score) : null,
            'status' => is_string($status) && trim($status) !== '' ? trim($status) : null,
            'type' => $type,
            'totalChapters' => $chapters,
        ];
    }

    /**
     * Sortir lokal per halaman; field yang tak ada dianggap setara
     * (urutan situs dipertahankan) — tanpa index penuh tak bisa lebih baik.
     *
     * @param list<array<string,mixed>> $items
     * @return list<array<string,mixed>>
     */
    protected function sortCards(array $items, string $orderBy, string $order): array
    {
        if ($orderBy === 'lastUpdated') {
            return $order === 'asc' ? array_reverse($items) : $items;
        }

        $dir = $order === 'asc' ? 1 : -1;

        usort($items, function ($a, $b) use ($orderBy, $dir) {
            $va = $a[$orderBy] ?? null;
            $vb = $b[$orderBy] ?? null;

            if ($va === null || $vb === null || $va === $vb) {
                return 0;
            }

            if (is_numeric($va) && is_numeric($vb)) {
                return $dir * ((float) $va <=> (float) $vb);
            }

            return $dir * strcasecmp((string) $va, (string) $vb);
        });

        return $items;
    }

    /**
     * Daftar genre dari https://komiktap.info/genres/ (ul.taxindex > li).
     *
     * @return list<array{slug:string,name:string,count:int}>
     */
    public function genres(): array
    {
        $html = $this->catalog->fetchSiteHtml('/genres/');

        $doc = $this->loadHtml($html);
        $xpath = new DOMXPath($doc);

        /** @var list<array{slug:string,name:string,count:int}> $genres */
        $genres = [];
        $seen = [];

        $nodes = $xpath->query('//ul[contains(concat(" ", normalize-space(@class), " "), " taxindex ")]/li/a[@href]');
        if ($nodes === false) {
            throw new CatalogException('Gagal memparse daftar genre.', 502);
        }

        foreach ($nodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $href = trim($node->getAttribute('href'));
            $slug = $this->genreSlugFromUrl($href);

            if ($slug === null || $slug === '' || isset($seen[$slug])) {
                continue;
            }
            $seen[$slug] = true;

            $genres[] = [
                'slug' => $slug,
                'name' => $this->genreName($node),
                'count' => $this->genreCount($node),
            ];
        }

        return $genres;
    }

    /**
     * Daftar komik per huruf dari /a-z-list/?show=X (+ pagination).
     *
     * @return array{letter:string,letters:list<string>,items:list<array{id:string,title:string,coverUrl:string}>,pagination:array{page:int,totalPages:int,hasNext:bool,hasPrevious:bool}}
     */
    public function azList(string $letter, int $page = 1): array
    {
        $letter = strtoupper(trim($letter));
        $page = max(1, $page);

        if (! preg_match('/^(?:[A-Z]|0-9|#)$/', $letter)) {
            throw new CatalogException('Huruf tidak valid.', 422);
        }

        $show = $letter === '#' ? '.' : $letter;
        $path = $page <= 1
            ? '/a-z-list/?show='.rawurlencode($show)
            : '/a-z-list/page/'.$page.'/?show='.rawurlencode($show);

        $html = $this->catalog->fetchSiteHtml($path);

        $doc = $this->loadHtml($html);
        $xpath = new DOMXPath($doc);

        $letters = $this->azLetters($xpath);

        $items = [];
        $itemNodes = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " bs ")]');
        if ($itemNodes !== false) {
            foreach ($itemNodes as $node) {
                if (! $node instanceof DOMElement) {
                    continue;
                }

                $item = $this->azItem($node, $xpath);
                if ($item !== null) {
                    $items[] = $item;
                }
            }
        }

        return [
            'letter' => $letter,
            'letters' => $letters,
            'items' => $items,
            'pagination' => $this->azPagination($xpath, $page),
        ];
    }

    /**
     * @return list<string>
     */
    protected function azLetters(DOMXPath $xpath): array
    {
        $letters = [];

        $nodes = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " lista ")]//a');
        if ($nodes === false) {
            return $letters;
        }

        foreach ($nodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $text = strtoupper(trim($node->textContent));
            if ($text !== '') {
                $letters[] = $text;
            }
        }

        return array_values(array_unique($letters));
    }

    /**
     * @return array{id:string,title:string,coverUrl:string}|null
     */
    protected function azItem(DOMElement $node, DOMXPath $xpath): ?array
    {
        $link = $xpath->query('.//a[@href]', $node)->item(0);
        if (! $link instanceof DOMElement) {
            return null;
        }

        $href = trim($link->getAttribute('href'));
        $title = trim($link->getAttribute('title'));

        if ($title === '') {
            $title = trim($link->textContent);
        }

        $slug = $this->mangaSlugFromUrl($href);

        if ($slug === null || $title === '') {
            return null;
        }

        $cover = '';
        $img = $xpath->query('.//img[@src]', $node)->item(0);
        if ($img instanceof DOMElement) {
            $cover = trim($img->getAttribute('src'));
        }

        return [
            'id' => $slug,
            'title' => html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'coverUrl' => $this->catalog->imageUrl($cover),
        ];
    }

    /**
     * @return array{page:int,totalPages:int,hasNext:bool,hasPrevious:bool}
     */
    protected function azPagination(DOMXPath $xpath, int $page): array
    {
        $totalPages = $page;
        $hasNext = false;
        $hasPrevious = $page > 1;

        $nodes = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " pagination ")]//a[contains(concat(" ", normalize-space(@class), " "), " page-numbers ")]');
        if ($nodes !== false) {
            foreach ($nodes as $node) {
                if (! $node instanceof DOMElement) {
                    continue;
                }

                $text = trim($node->textContent);
                if (ctype_digit($text)) {
                    $totalPages = max($totalPages, (int) $text);
                }

                $class = ' '.$node->getAttribute('class').' ';
                if (str_contains($class, ' next ')) {
                    $hasNext = true;
                }
                if (str_contains($class, ' prev ')) {
                    $hasPrevious = true;
                }
            }
        }

        return [
            'page' => $page,
            'totalPages' => $totalPages,
            'hasNext' => $hasNext,
            'hasPrevious' => $hasPrevious,
        ];
    }

    protected function genreSlugFromUrl(string $url): ?string
    {
        $path = parse_url(trim($url), PHP_URL_PATH);
        if (! is_string($path)) {
            return null;
        }

        if (! preg_match('#/genres/([^/]+)/?#', $path, $m)) {
            return null;
        }

        return trim($m[1]);
    }

    protected function genreName(DOMElement $link): string
    {
        $span = $link->getElementsByTagName('span')->item(0);
        $name = $span instanceof DOMElement ? trim($span->textContent) : trim($link->textContent);

        return html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    protected function genreCount(DOMElement $link): int
    {
        $italic = $link->getElementsByTagName('i')->item(0);
        if (! $italic instanceof DOMElement) {
            return 0;
        }

        return (int) preg_replace('/[^0-9]/', '', $italic->textContent);
    }

    protected function mangaSlugFromUrl(string $url): ?string
    {
        $path = parse_url(trim($url), PHP_URL_PATH);
        if (! is_string($path)) {
            return null;
        }

        if (! preg_match('#/manga/([^/]+)/?#', $path, $m)) {
            return null;
        }

        return trim($m[1]);
    }

    protected function loadHtml(string $html): DOMDocument
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NONET);
        libxml_clear_errors();

        return $doc;
    }
}
