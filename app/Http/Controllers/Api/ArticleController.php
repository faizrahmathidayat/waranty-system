<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\CmsMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
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

        $query = Article::published()->forSite($site)->with('media');

        $category = trim((string) $request->query('category', ''));
        if ($category !== '') {
            $query->whereRaw('LOWER(category) = ?', [mb_strtolower($category)]);
        }

        $articles = $query->orderByDesc('published_at')->paginate($perPage);

        return response()->json([
            'data' => $articles->getCollection()->map(function (Article $article) {
                return $this->summarize($article);
            })->all(),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    /**
     * Distinct categories that currently have at least one published article for
     * the site, so a consumer can build its filter buttons without hardcoding them.
     */
    public function categories(Request $request)
    {
        $site = $this->validateSite($request);
        if ($site === null) {
            return response()->json(['message' => 'Parameter site wajib diisi dengan glosspro atau lexent.'], 400);
        }

        $categories = [];
        foreach (Article::published()->forSite($site)->whereNotNull('category')->pluck('category') as $category) {
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

        $article = Article::published()->forSite($site)->with('media')->where('slug', $slug)->first();

        if (!$article) {
            return response()->json(['message' => 'Artikel tidak ditemukan.'], 404);
        }

        $payload = $this->summarize($article);
        $payload['body'] = $article->body;
        $payload['tags'] = $article->tags ?: [];
        $payload['media'] = $article->media->map(function (CmsMedia $media) {
            return $this->mediaPayload($media);
        })->all();

        return response()->json(['data' => $payload]);
    }

    private function validateSite(Request $request): ?string
    {
        $site = $request->query('site');
        return in_array($site, self::ALLOWED_SITES, true) ? $site : null;
    }

    private function summarize(Article $article): array
    {
        $cover = $article->media->first();
        $excerpt = $article->excerpt ?: Str::limit(trim(strip_tags($article->body)), 160);

        return [
            'slug' => $article->slug,
            'title' => $article->title,
            'excerpt' => $excerpt,
            'category' => $article->category,
            'published_at' => optional($article->published_at)->toIso8601String(),
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
