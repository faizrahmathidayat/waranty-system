<?php

namespace Tests\Unit;

use App\Models\PortfolioItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioItemModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_relation_uses_the_short_morph_map_alias(): void
    {
        $item = PortfolioItem::create([
            'title' => 'Proyek Uji', 'slug' => 'proyek-uji', 'body' => '<p>Isi</p>',
            'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => true,
            'location' => 'Jakarta',
        ]);

        $media = $item->media()->create([
            'path' => 'cms/portfolio/1/a.webp', 'thumbnail_path' => 'cms/portfolio/1/a-thumb.webp',
            'width' => 1920, 'height' => 1080, 'sort_order' => 0,
        ]);

        $this->assertSame('portfolio', $media->fresh()->mediable_type);
        $this->assertTrue($item->media->first()->is($media));
        $this->assertSame('Jakarta', $item->fresh()->location);
    }

    public function test_published_scope_excludes_drafts_and_future_dated_items(): void
    {
        PortfolioItem::create(['title' => 'Draft', 'slug' => 'draft', 'body' => 'x', 'status' => 'draft', 'show_on_glosspro' => true]);
        PortfolioItem::create(['title' => 'Future', 'slug' => 'future', 'body' => 'x', 'status' => 'published', 'published_at' => now()->addDay(), 'show_on_glosspro' => true]);
        $live = PortfolioItem::create(['title' => 'Live', 'slug' => 'live', 'body' => 'x', 'status' => 'published', 'published_at' => now()->subHour(), 'show_on_glosspro' => true]);

        $result = PortfolioItem::published()->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($live));
    }

    public function test_for_site_scope_filters_by_the_matching_boolean_column(): void
    {
        $glosspro = PortfolioItem::create(['title' => 'A', 'slug' => 'a', 'body' => 'x', 'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => true, 'show_on_lexent' => false]);
        PortfolioItem::create(['title' => 'B', 'slug' => 'b', 'body' => 'x', 'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => false, 'show_on_lexent' => true]);

        $result = PortfolioItem::published()->forSite('glosspro')->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($glosspro));
    }
}
