<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CatalogItem;
use App\Models\CmsMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CatalogController extends Controller
{
    private const ALLOWED_SITES = ['glosspro', 'lexent'];
    private const DEFAULT_PER_PAGE = 9;
    private const MAX_PER_PAGE = 30;

    public function index(Request $request)
    {
        $site = $this->validateSite($request);
        if ($site === null) {
            return response()->json(['message' => 'Parameter site wajib diisi dengan glosspro atau lexent.'], 400);
        }

        $perPage = (int) $request->query('per_page', self::DEFAULT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, self::MAX_PER_PAGE) : self::DEFAULT_PER_PAGE;

        $query = CatalogItem::published()->forSite($site)->with('media');

        $category = trim((string) $request->query('category', ''));
        if ($category !== '') {
            $query->whereRaw('LOWER(category) = ?', [mb_strtolower($category)]);
        }

        $product = trim((string) $request->query('product', ''));
        if ($product !== '') {
            $query->whereRaw('LOWER(product) = ?', [mb_strtolower($product)]);
        }

        $items = $query->orderByDesc('published_at')->paginate($perPage);

        return response()->json([
            'data' => $items->getCollection()->map(function (CatalogItem $item) {
                return $this->summarize($item);
            })->all(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    /**
     * Navbar menu for a site: every product with the distinct categories that
     * currently have a published item under it, e.g.
     * {"Kaca Film Bangunan": ["Black Vision", ...], "Kaca Film Mobil": [], "PPF": []}.
     */
    public function menu(Request $request)
    {
        $site = $this->validateSite($request);
        if ($site === null) {
            return response()->json(['message' => 'Parameter site wajib diisi dengan glosspro atau lexent.'], 400);
        }

        $menu = array_fill_keys(CatalogItem::PRODUCTS, []);
        $rows = CatalogItem::published()->forSite($site)
            ->whereIn('product', CatalogItem::PRODUCTS)
            ->whereNotNull('category')
            ->orderBy('category')
            ->get(['product', 'category']);

        foreach ($rows as $row) {
            $category = trim($row->category);
            if ($category === '') {
                continue;
            }
            $key = mb_strtolower($category);
            if (!isset($menu[$row->product][$key])) {
                $menu[$row->product][$key] = $category;
            }
        }

        foreach ($menu as $product => $categories) {
            $categories = array_values($categories);
            sort($categories, SORT_NATURAL | SORT_FLAG_CASE);
            $menu[$product] = $categories;
        }

        return response()->json(['data' => $menu]);
    }

    public function show(Request $request, string $slug)
    {
        $site = $this->validateSite($request);
        if ($site === null) {
            return response()->json(['message' => 'Parameter site wajib diisi dengan glosspro atau lexent.'], 400);
        }

        $item = CatalogItem::published()->forSite($site)->with('media')->where('slug', $slug)->first();

        if (!$item) {
            return response()->json(['message' => 'Item katalog tidak ditemukan.'], 404);
        }

        $payload = $this->summarize($item);
        $payload['body'] = $item->body;
        $payload['media'] = $item->media->map(function (CmsMedia $media) {
            return $this->mediaPayload($media);
        })->all();

        return response()->json(['data' => $payload]);
    }

    private function validateSite(Request $request): ?string
    {
        $site = $request->query('site');
        return in_array($site, self::ALLOWED_SITES, true) ? $site : null;
    }

    private function summarize(CatalogItem $item): array
    {
        $cover = $item->media->first();
        $excerpt = $item->excerpt ?: Str::limit(trim(strip_tags($item->body)), 160);

        return [
            'slug' => $item->slug,
            'title' => $item->title,
            'excerpt' => $excerpt,
            'category' => $item->category,
            'product' => $item->product,
            'spec_highlights' => $item->spec_highlights ?: [],
            'published_at' => optional($item->published_at)->toIso8601String(),
            'cover' => $cover ? $this->mediaPayload($cover) : null,
        ];
    }

    private function mediaPayload(CmsMedia $media): array
    {
        return [
            'url' => $media->url,
            'thumbnail_url' => $media->thumbnail_url,
            'width' => $media->width,
            'height' => $media->height,
            'alt_text' => $media->alt_text,
        ];
    }
}
