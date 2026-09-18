<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\CmsMedia;
use App\Models\PortfolioItem;
use App\Services\ImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class PortfolioController extends Controller
{
    private ImageProcessor $imageProcessor;

    public function __construct(ImageProcessor $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
    }

    public function index()
    {
        return view('cms.portfolio.index', [
            'title' => 'Portofolio',
            'navbar' => 'CMS - Portofolio',
        ]);
    }

    public function data()
    {
        return DataTables::of(PortfolioItem::query()->orderByDesc('id'))
            ->addColumn('cover', function (PortfolioItem $item) {
                $cover = $item->media()->first();
                return $cover ? $cover->thumbnail_url : null;
            })
            ->addColumn('sites', function (PortfolioItem $item) {
                $sites = [];
                if ($item->show_on_glosspro) {
                    $sites[] = 'GlossPro';
                }
                if ($item->show_on_lexent) {
                    $sites[] = 'LEXENT';
                }
                return implode(', ', $sites) ?: '-';
            })
            ->toJson();
    }

    public function show(PortfolioItem $portfolioItem)
    {
        $portfolioItem->load('media');
        return response()->json($portfolioItem);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = auth()->id();

        $item = PortfolioItem::create($data);
        $this->storeUploadedImages($item, $request);

        return response()->json(['success' => true, 'id' => $item->id]);
    }

    public function update(Request $request, PortfolioItem $portfolioItem)
    {
        $data = $this->validated($request, $portfolioItem->id);

        $portfolioItem->update($data);
        $this->storeUploadedImages($portfolioItem, $request);

        return response()->json(['success' => true]);
    }

    public function destroy(PortfolioItem $portfolioItem)
    {
        foreach ($portfolioItem->media as $media) {
            $this->deleteMediaFiles($media);
        }

        $portfolioItem->delete();

        return response()->json(['success' => true]);
    }

    public function destroyMedia(CmsMedia $media)
    {
        $this->deleteMediaFiles($media);

        return response()->json(['success' => true]);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = 'required|string|max:255|unique:cms_portfolio_items,slug';
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
            'location' => 'nullable|string|max:255',
        ]);

        $data['show_on_glosspro'] = $request->boolean('show_on_glosspro');
        $data['show_on_lexent'] = $request->boolean('show_on_lexent');

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    private function storeUploadedImages(PortfolioItem $item, Request $request): void
    {
        if (!$request->hasFile('images')) {
            return;
        }

        $nextOrder = (int) $item->media()->max('sort_order') + 1;

        foreach ($request->file('images') as $file) {
            $processed = $this->imageProcessor->process($file, 'cms/portfolio/' . $item->id);

            $item->media()->create(array_merge($processed, [
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
