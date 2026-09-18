<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\CmsMedia;
use App\Services\ImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ArticleController extends Controller
{
    private ImageProcessor $imageProcessor;

    public function __construct(ImageProcessor $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
    }

    public function index()
    {
        return view('cms.articles.index', [
            'title' => 'Artikel',
            'navbar' => 'CMS - Artikel',
        ]);
    }

    public function data()
    {
        return DataTables::of(Article::query()->orderByDesc('id'))
            ->addColumn('cover', function (Article $article) {
                $cover = $article->media()->first();
                return $cover ? $cover->thumbnail_url : null;
            })
            ->addColumn('sites', function (Article $article) {
                $sites = [];
                if ($article->show_on_glosspro) {
                    $sites[] = 'GlossPro';
                }
                if ($article->show_on_lexent) {
                    $sites[] = 'LEXENT';
                }
                return implode(', ', $sites) ?: '-';
            })
            ->toJson();
    }

    public function show(Article $article)
    {
        $article->load('media');
        return response()->json($article);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = auth()->id();

        $article = Article::create($data);
        $this->storeUploadedImages($article, $request);

        return response()->json(['success' => true, 'id' => $article->id]);
    }

    public function update(Request $request, Article $article)
    {
        $data = $this->validated($request, $article->id);

        $article->update($data);
        $this->storeUploadedImages($article, $request);

        return response()->json(['success' => true]);
    }

    public function destroy(Article $article)
    {
        foreach ($article->media as $media) {
            $this->deleteMediaFiles($media);
        }

        $article->delete();

        return response()->json(['success' => true]);
    }

    public function destroyMedia(CmsMedia $media)
    {
        $this->deleteMediaFiles($media);

        return response()->json(['success' => true]);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = 'required|string|max:255|unique:cms_articles,slug';
        if ($ignoreId !== null) {
            $slugRule .= ',' . $ignoreId;
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => $slugRule,
            'excerpt' => 'nullable|string|max:500',
            'body' => 'required|string',
            'category' => 'nullable|string|max:100',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
        ]);

        $data['show_on_glosspro'] = $request->boolean('show_on_glosspro');
        $data['show_on_lexent'] = $request->boolean('show_on_lexent');

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    private function storeUploadedImages(Article $article, Request $request): void
    {
        if (!$request->hasFile('images')) {
            return;
        }

        $nextOrder = (int) $article->media()->max('sort_order') + 1;

        foreach ($request->file('images') as $file) {
            $processed = $this->imageProcessor->process($file, 'cms/articles/' . $article->id);

            $article->media()->create(array_merge($processed, [
                'sort_order' => $nextOrder,
            ]));

            $nextOrder++;
        }
    }

    private function deleteMediaFiles(CmsMedia $media): void
    {
        Storage::disk('public')->delete([$media->path, $media->thumbnail_path]);
        $media->delete();
    }
}
