<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\CatalogItem;
use App\Models\CmsMedia;
use App\Services\ImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class CatalogController extends Controller
{
    private ImageProcessor $imageProcessor;

    public function __construct(ImageProcessor $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
    }

    public function index()
    {
        return view('cms.catalog.index', [
            'title' => 'Katalog',
            'navbar' => 'CMS - Katalog',
        ]);
    }

    public function data()
    {
        return DataTables::of(CatalogItem::query()->orderByDesc('id'))
            ->addColumn('cover', function (CatalogItem $item) {
                $cover = $item->media()->first();
                return $cover ? $cover->thumbnail_url : null;
            })
            ->addColumn('sites', function (CatalogItem $item) {
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

    public function show(CatalogItem $catalogItem)
    {
        $catalogItem->load('media');
        return response()->json($catalogItem);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = auth()->id();

        $item = CatalogItem::create($data);
        $this->storeUploadedImages($item, $request);

        return response()->json(['success' => true, 'id' => $item->id]);
    }

    public function update(Request $request, CatalogItem $catalogItem)
    {
        $data = $this->validated($request, $catalogItem->id);

        $catalogItem->update($data);
        $this->storeUploadedImages($catalogItem, $request);

        return response()->json(['success' => true]);
    }

    public function destroy(CatalogItem $catalogItem)
    {
        foreach ($catalogItem->media as $media) {
            $this->deleteMediaFiles($media);
        }

        $catalogItem->delete();

        return response()->json(['success' => true]);
    }

    public function destroyMedia(CmsMedia $media)
    {
        $this->deleteMediaFiles($media);

        return response()->json(['success' => true]);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = 'required|string|max:255|unique:cms_catalog_items,slug';
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
            'spec_highlights' => 'nullable|array',
            'spec_highlights.*.label' => 'nullable|string|max:100',
            'spec_highlights.*.value' => 'nullable|string|max:100',
        ]);

        $data['show_on_glosspro'] = $request->boolean('show_on_glosspro');
        $data['show_on_lexent'] = $request->boolean('show_on_lexent');
        $data['spec_highlights'] = $this->normalizedHighlights($request->input('spec_highlights', []));

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    private function normalizedHighlights(array $rows): array
    {
        return array_values(array_filter(array_map(function ($row) {
            $label = trim($row['label'] ?? '');
            $value = trim($row['value'] ?? '');

            return ($label === '' && $value === '') ? null : ['label' => $label, 'value' => $value];
        }, $rows)));
    }

    private function storeUploadedImages(CatalogItem $item, Request $request): void
    {
        if (!$request->hasFile('images')) {
            return;
        }

        $nextOrder = (int) $item->media()->max('sort_order') + 1;

        foreach ($request->file('images') as $file) {
            $processed = $this->imageProcessor->process($file, 'cms/catalog/' . $item->id);

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
