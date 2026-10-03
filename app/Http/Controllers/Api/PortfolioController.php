<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CmsMedia;
use App\Models\PortfolioItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PortfolioController extends Controller
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

        $query = PortfolioItem::published()->forSite($site)->with('media');

        $category = trim((string) $request->query('category', ''));
        if ($category !== '') {
            $query->whereRaw('LOWER(category) = ?', [mb_strtolower($category)]);
        }

        $items = $query->orderByDesc('published_at')->paginate($perPage);

        return response()->json([
            'data' => $items->getCollection()->map(function (PortfolioItem $item) {
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
     * Distinct categories that currently have at least one published item for the
     * site, so a consumer can build its filter buttons without hardcoding them.
     */
    public function categories(Request $request)
    {
        $site = $this->validateSite($request);
        if ($site === null) {
            return response()->json(['message' => 'Parameter site wajib diisi dengan glosspro atau lexent.'], 400);
        }

        $categories = [];
        foreach (PortfolioItem::published()->forSite($site)->whereNotNull('category')->pluck('category') as $category) {
            $category = trim($category);
            if ($category !== '') {
                $categories[mb_strtolower($category)] = $categories[mb_strtolower($category)] ?? $category;
            }
        }

        $categories = array_values($categories);
        sort($categories, SORT_NATURAL | SORT_FLAG_CASE);

        return response()->json(['data' => $categories]);
    }

    public function show(Request $request, string $slug)
    {
        $site = $this->validateSite($request);
        if ($site === null) {
            return response()->json(['message' => 'Parameter site wajib diisi dengan glosspro atau lexent.'], 400);
        }

        $item = PortfolioItem::published()->forSite($site)->with('media')->where('slug', $slug)->first();

        if (!$item) {
            return response()->json(['message' => 'Item portofolio tidak ditemukan.'], 404);
        }

        $payload = $this->summarize($item);
        $payload['body'] = $item->body;
        $payload['location'] = $item->location;
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

    private function summarize(PortfolioItem $item): array
    {
        $cover = $item->media->first();
        $excerpt = $item->excerpt ?: Str::limit(trim(strip_tags($item->body)), 160);

        return [
            'slug' => $item->slug,
            'title' => $item->title,
            'excerpt' => $excerpt,
            'category' => $item->category,
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
