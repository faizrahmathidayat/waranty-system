<?php

namespace Tests\Unit;

use App\Models\CatalogItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogItemModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_relation_uses_the_short_morph_map_alias(): void
    {
        $item = CatalogItem::create([
            'title' => 'Produk Uji', 'slug' => 'produk-uji', 'body' => '<p>Isi</p>',
            'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => true,
        ]);

        $media = $item->media()->create([
            'path' => 'cms/catalog/1/a.webp', 'thumbnail_path' => 'cms/catalog/1/a-thumb.webp',
            'width' => 1920, 'height' => 1080, 'sort_order' => 0,
        ]);

        $this->assertSame('catalog', $media->fresh()->mediable_type);
        $this->assertTrue($item->media->first()->is($media));
    }

    public function test_published_scope_excludes_drafts_and_future_dated_items(): void
    {
        CatalogItem::create(['title' => 'Draft', 'slug' => 'draft', 'body' => 'x', 'status' => 'draft', 'show_on_glosspro' => true]);
        CatalogItem::create(['title' => 'Future', 'slug' => 'future', 'body' => 'x', 'status' => 'published', 'published_at' => now()->addDay(), 'show_on_glosspro' => true]);
        $live = CatalogItem::create(['title' => 'Live', 'slug' => 'live', 'body' => 'x', 'status' => 'published', 'published_at' => now()->subHour(), 'show_on_glosspro' => true]);

        $result = CatalogItem::published()->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($live));
    }

    public function test_for_site_scope_filters_by_the_matching_boolean_column(): void
    {
        $glosspro = CatalogItem::create(['title' => 'A', 'slug' => 'a', 'body' => 'x', 'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => true, 'show_on_lexent' => false]);
        CatalogItem::create(['title' => 'B', 'slug' => 'b', 'body' => 'x', 'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => false, 'show_on_lexent' => true]);

        $result = CatalogItem::published()->forSite('glosspro')->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($glosspro));
    }

    public function test_spec_highlights_round_trips_as_a_plain_array(): void
    {
        $item = CatalogItem::create([
            'title' => 'Dengan Spek', 'slug' => 'dengan-spek', 'body' => 'x', 'status' => 'draft',
            'spec_highlights' => [['label' => 'VLT', 'value' => '20%'], ['label' => 'Garansi', 'value' => '5 Thn']],
        ]);

        $fresh = $item->fresh();

        $this->assertIsArray($fresh->spec_highlights);
        $this->assertSame('VLT', $fresh->spec_highlights[0]['label']);
        $this->assertSame('5 Thn', $fresh->spec_highlights[1]['value']);
    }
}
