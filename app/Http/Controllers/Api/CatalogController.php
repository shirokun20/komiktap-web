<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CatalogException;
use App\Http\Controllers\Controller;
use App\Services\CatalogService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CatalogService $catalog,
        protected \App\Services\CatalogHtmlService $html,
    ) {}

    /**
     * GET /api/v2/catalog/comics
     */
    public function comics(Request $request)
    {
        $type = $request->query('type');
        $type = is_string($type) && $type !== '' ? mb_strtolower(trim($type)) : null;
        if ($type !== null && ! isset(\App\Services\CatalogHtmlService::TYPE_PATHS[$type])) {
            return $this->error('Nilai type tidak valid. Gunakan: manga, manhua, manhwa.', 400);
        }

        $status = $request->query('status');
        $status = is_string($status) && $status !== '' ? mb_strtolower(trim($status)) : null;
        if ($status !== null && ! in_array($status, ['ongoing', 'completed', 'complete'], true)) {
            return $this->error('Nilai status tidak valid. Gunakan: ongoing, completed.', 400);
        }
        if ($status === 'complete') {
            $status = 'completed';
        }

        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1|max:10000',
            'perPage' => 'nullable|integer|min:1|max:100',
            'orderBy' => 'nullable|string|in:lastUpdated,title,rating,views,totalChapters',
            'order' => 'nullable|string|in:asc,desc',
        ]);

        // Upstream resmi tak dukung filter tipe/status: layani dari
        // halaman list situs yang memang sudah terfilter natively.
        if ($type !== null || $status !== null) {
            try {
                $result = $this->html->filteredList(
                    $type,
                    $status,
                    (string) ($validated['search'] ?? ''),
                    (int) ($validated['page'] ?? 1),
                    (int) ($validated['perPage'] ?? 20),
                    (string) ($validated['orderBy'] ?? 'lastUpdated'),
                    (string) ($validated['order'] ?? 'desc'),
                );
            } catch (CatalogException $e) {
                return $this->error($e->getMessage(), $e->status());
            }

            return $this->success($result);
        }

        try {
            $result = $this->catalog->comics([
                'search' => $validated['search'] ?? '',
                'page' => $validated['page'] ?? 1,
                'perPage' => $validated['perPage'] ?? 20,
                'orderBy' => $validated['orderBy'] ?? 'lastUpdated',
                'order' => $validated['order'] ?? 'desc',
            ]);
        } catch (CatalogException $e) {
            return $this->error($e->getMessage(), $e->status());
        }

        return $this->success($result);
    }

    /**
     * GET /api/v2/catalog/projects
     * Daftar Project garapan tim (sumber /project/ situs).
     */
    public function projects(Request $request)
    {
        $validated = $request->validate([
            'page' => 'nullable|integer|min:1|max:10000',
            'perPage' => 'nullable|integer|min:1|max:100',
            'orderBy' => 'nullable|string|in:lastUpdated,title,rating,views,totalChapters',
            'order' => 'nullable|string|in:asc,desc',
        ]);

        try {
            $result = $this->html->projectsList(
                (int) ($validated['page'] ?? 1),
                (int) ($validated['perPage'] ?? 20),
                (string) ($validated['orderBy'] ?? 'lastUpdated'),
                (string) ($validated['order'] ?? 'desc'),
            );
        } catch (CatalogException $e) {
            return $this->error($e->getMessage(), $e->status());
        }

        return $this->success($result);
    }

    /**
     * GET /api/v2/catalog/genres/{slug}
     * Endpoint khusus content-by-tag agar tidak mengganggu search umum.
     */
    public function byGenre(Request $request, string $slug)
    {
        if (! preg_match('/^[a-z0-9][a-z0-9-]*$/', $slug)) {
            return $this->error('Genre tidak valid.', 422);
        }

        $validated = $request->validate([
            'page' => 'nullable|integer|min:1|max:10000',
            'perPage' => 'nullable|integer|min:1|max:100',
            'orderBy' => 'nullable|string|in:lastUpdated,title,rating,views,totalChapters',
            'order' => 'nullable|string|in:asc,desc',
        ]);

        try {
            $result = $this->catalog->byGenre($slug, [
                'page' => $validated['page'] ?? 1,
                'perPage' => $validated['perPage'] ?? 20,
                'orderBy' => $validated['orderBy'] ?? 'lastUpdated',
                'order' => $validated['order'] ?? 'desc',
            ]);
        } catch (CatalogException $e) {
            return $this->error($e->getMessage(), $e->status());
        }

        return $this->success($result);
    }

    /**
     * GET /api/v2/catalog/comics/{id}
     */
    public function show(string $id)
    {
        if (! preg_match('/^[a-z0-9][a-z0-9-]*$/', $id)) {
            return $this->error('ID katalog tidak valid.', 422);
        }

        try {
            $detail = $this->catalog->detail($id);
        } catch (CatalogException $e) {
            return $this->error($e->getMessage(), $e->status());
        }

        return $this->success($detail);
    }

    /**
     * GET /api/v2/catalog/comics/{id}/chapters/{number}
     */
    public function chapter(string $id, string $number)
    {
        if (! preg_match('/^[a-z0-9][a-z0-9-]*$/', $id)) {
            return $this->error('ID katalog tidak valid.', 422);
        }

        if (! preg_match('/^[0-9]+(?:\.[0-9]+)?$/', $number)) {
            return $this->error('Nomor chapter tidak valid.', 422);
        }

        try {
            $chapter = $this->catalog->chapter($id, $number);
        } catch (CatalogException $e) {
            if ($e->status() === 404) {
                return $this->error('Chapter tidak ditemukan.', 404);
            }

            return $this->error($e->getMessage(), $e->status());
        }

        return $this->success($chapter);
    }

    /**
     * GET /api/v2/catalog/announcements
     */
    public function announcements()
    {
        try {
            $items = $this->catalog->announcements();
        } catch (CatalogException $e) {
            return $this->error($e->getMessage(), $e->status());
        }

        return $this->success($items);
    }

    /**
     * GET /api/v2/catalog/genres
     * Daftar genre dari halaman situs (API resmi tidak menyediakannya).
     */
    public function genres()
    {
        try {
            $items = $this->html->genres();
        } catch (CatalogException $e) {
            return $this->error($e->getMessage(), $e->status());
        }

        return $this->success($items);
    }

    /**
     * GET /api/v2/catalog/az?letter=A&page=1
     * Daftar komik per huruf dari halaman A-Z situs.
     */
    public function az(Request $request)
    {
        $validated = $request->validate([
            'letter' => 'nullable|string|max:3',
            'page' => 'nullable|integer|min:1|max:10000',
        ]);

        try {
            $result = $this->html->azList(
                $validated['letter'] ?? 'A',
                $validated['page'] ?? 1,
            );
        } catch (CatalogException $e) {
            return $this->error($e->getMessage(), $e->status());
        }

        return $this->success($result);
    }

    /**
     * GET /api/v2/catalog/image?u=<url>
     * Proxy bytes gambar agar tidak kena blokir hotlink.
     */
    public function image(Request $request)
    {
        $validated = $request->validate([
            'u' => 'required|string|max:2048',
        ]);

        try {
            $image = $this->catalog->fetchImage($validated['u']);
        } catch (CatalogException $e) {
            return $this->error($e->getMessage(), $e->status());
        }

        return response($image['body'], 200, [
            'Content-Type' => $image['content_type'],
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
