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

        $articles = Article::published()
            ->forSite($site)
            ->with('media')
            ->orderByDesc('published_at')
            ->paginate($perPage);

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
