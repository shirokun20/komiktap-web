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
