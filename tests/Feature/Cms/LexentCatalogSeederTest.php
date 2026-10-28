<?php

namespace Tests\Feature\Cms;

use App\Models\CatalogItem;
use App\Models\CmsMedia;
use Database\Seeders\LexentCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LexentCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    private function item(string $slug, array $flags): CatalogItem
    {
        return CatalogItem::create($flags + ['title' => $slug, 'slug' => $slug, 'body' => 'x', 'status' => 'published', 'published_at' => now()]);
    }

    public function test_it_replaces_lexent_items_and_leaves_other_sites_alone(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('cms/catalog/old.webp', 'x');
        Storage::disk('public')->put('cms/catalog/old-thumb.webp', 'x');

        $old = $this->item('old-lexent', ['show_on_lexent' => true, 'product' => 'PPF']);
        $old->media()->create(['path' => 'cms/catalog/old.webp', 'thumbnail_path' => 'cms/catalog/old-thumb.webp', 'width' => 1, 'height' => 1, 'sort_order' => 0]);
        $shared = $this->item('shared', ['show_on_lexent' => true, 'show_on_glosspro' => true, 'product' => 'PPF']);
        $this->item('glosspro-only', ['show_on_glosspro' => true]);

        $this->seed(LexentCatalogSeeder::class);

        $this->assertNull(CatalogItem::where('slug', 'old-lexent')->first());
        $this->assertSame(0, CmsMedia::count());
        Storage::disk('public')->assertMissing(['cms/catalog/old.webp', 'cms/catalog/old-thumb.webp']);

        $shared->refresh();
        $this->assertFalse($shared->show_on_lexent);
        $this->assertTrue($shared->show_on_glosspro);
        $this->assertNull($shared->product);
        $this->assertNotNull(CatalogItem::where('slug', 'glosspro-only')->first());

        $this->assertSame(36, CatalogItem::where('show_on_lexent', true)->count());
        $this->assertSame(18, CatalogItem::where('product', 'Kaca Film Mobil')->count());
        $this->assertSame(14, CatalogItem::where('product', 'Kaca Film Bangunan')->count());
        $this->assertSame(4, CatalogItem::where('product', 'PPF')->count());
    }

    public function test_running_it_twice_gives_the_same_catalog_and_every_item_is_publishable(): void
    {
        $this->seed(LexentCatalogSeeder::class);
        $this->seed(LexentCatalogSeeder::class);

        $this->assertSame(36, CatalogItem::count());
        $this->assertSame(0, CatalogItem::where('show_on_glosspro', true)->count());
        $this->assertSame(36, CatalogItem::published()->forSite('lexent')->count());

        $item = CatalogItem::where('slug', 'lexent-bv-05')->firstOrFail();
        $this->assertSame('LEXENT Black Vision 05', $item->title);
        $this->assertSame('Black Vision', $item->category);
        $this->assertSame([['label' => 'VLT', 'value' => '5%'], ['label' => 'TSER', 'value' => '62%']], array_slice($item->spec_highlights, 0, 2));
    }

    public function test_the_menu_api_lists_the_series_and_types_as_categories(): void
    {
        $this->seed(LexentCatalogSeeder::class);

        $this->getJson('/api/cms/catalog-menu?site=lexent', ['X-API-Key' => config('services.cms.api_key')])
            ->assertOk()
            ->assertExactJson(['data' => [
                'Kaca Film Bangunan' => ['Black Vision', 'High Performance', 'Reflective Series', 'Ultra Protect'],
                'Kaca Film Mobil' => ['BP Series', 'HT Series', 'IR99 Series', 'MK Series'],
                'PPF' => ['Type L', 'Type L Matte', 'Type S', 'Type T Plus'],
            ]]);
    }
}
