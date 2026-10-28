<?php

namespace Tests\Feature\Api;

use App\Models\CatalogItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    private function headers(): array
    {
        return ['X-API-Key' => config('services.cms.api_key')];
    }

    public function test_list_requires_api_key(): void
    {
        $this->getJson('/api/cms/catalog?site=glosspro')->assertStatus(401);
    }

    public function test_list_requires_a_valid_site_parameter(): void
    {
        $this->getJson('/api/cms/catalog?site=nope', $this->headers())->assertStatus(400);
    }

    public function test_list_returns_only_published_items_for_the_requested_site(): void
    {
        CatalogItem::create(['title' => 'Glosspro Live', 'slug' => 'glosspro-live', 'body' => 'x', 'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => true]);
        CatalogItem::create(['title' => 'Lexent Only', 'slug' => 'lexent-only', 'body' => 'x', 'status' => 'published', 'published_at' => now(), 'show_on_lexent' => true]);
        CatalogItem::create(['title' => 'Draft', 'slug' => 'draft', 'body' => 'x', 'status' => 'draft', 'show_on_glosspro' => true]);

        $response = $this->getJson('/api/cms/catalog?site=glosspro', $this->headers());

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.slug', 'glosspro-live');
        $response->assertJsonStructure(['data' => [['slug', 'title', 'excerpt', 'category', 'published_at', 'cover']], 'meta' => ['current_page', 'last_page', 'per_page', 'total']]);
    }

    public function test_list_can_be_filtered_by_category_ignoring_case(): void
    {
        foreach ([['A', 'Kaca Film Bangunan'], ['B', 'kaca film bangunan'], ['C', 'Kaca Film Mobil'], ['D', null]] as [$title, $category]) {
            CatalogItem::create(['title' => $title, 'slug' => strtolower($title), 'body' => 'x', 'category' => $category, 'status' => 'published', 'published_at' => now(), 'show_on_lexent' => true]);
        }

        $response = $this->getJson('/api/cms/catalog?site=lexent&category=Kaca%20Film%20Bangunan', $this->headers());

        $response->assertOk();
        $this->assertEqualsCanonicalizing(['a', 'b'], array_column($response->json('data'), 'slug'));
        $this->getJson('/api/cms/catalog?site=lexent', $this->headers())->assertJsonCount(4, 'data');
    }

    public function test_list_can_be_filtered_by_product(): void
    {
        foreach ([['a', 'Kaca Film Bangunan'], ['b', 'PPF'], ['c', null]] as [$slug, $product]) {
            CatalogItem::create(['title' => $slug, 'slug' => $slug, 'body' => 'x', 'product' => $product, 'status' => 'published', 'published_at' => now(), 'show_on_lexent' => true]);
        }

        $response = $this->getJson('/api/cms/catalog?site=lexent&product=PPF', $this->headers());

        $response->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', 'b')->assertJsonPath('data.0.product', 'PPF');
    }

    public function test_menu_groups_published_categories_by_product(): void
    {
        $make = fn ($slug, $product, $category, $extra = []) => CatalogItem::create($extra + ['title' => $slug, 'slug' => $slug, 'body' => 'x', 'product' => $product, 'category' => $category, 'status' => 'published', 'published_at' => now(), 'show_on_lexent' => true]);
        $make('a', 'Kaca Film Bangunan', 'Reflective Series');
        $make('b', 'Kaca Film Bangunan', 'Black Vision');
        $make('c', 'Kaca Film Bangunan', 'black vision');
        $make('d', 'PPF', 'Type S');
        $make('e', 'Kaca Film Mobil', 'HT Series', ['status' => 'draft']);
        $make('f', 'Kaca Film Mobil', 'MK Series', ['show_on_lexent' => false, 'show_on_glosspro' => true]);
        $make('g', null, 'Tanpa Product');

        $response = $this->getJson('/api/cms/catalog-menu?site=lexent', $this->headers());

        $response->assertOk()->assertExactJson(['data' => [
            'Kaca Film Bangunan' => ['Black Vision', 'Reflective Series'],
            'Kaca Film Mobil' => [],
            'PPF' => ['Type S'],
        ]]);
        $this->getJson('/api/cms/catalog-menu?site=nope', $this->headers())->assertStatus(400);
        $this->getJson('/api/cms/catalog-menu?site=lexent')->assertStatus(401);
    }

    public function test_show_returns_body_and_spec_highlights_and_media(): void
    {
        $item = CatalogItem::create([
            'title' => 'Detail', 'slug' => 'detail', 'body' => '<p>Isi lengkap</p>', 'status' => 'published',
            'published_at' => now(), 'show_on_glosspro' => true,
            'spec_highlights' => [['label' => 'VLT', 'value' => '20%']],
        ]);
        $item->media()->create(['path' => 'cms/catalog/1/a.webp', 'thumbnail_path' => 'cms/catalog/1/a-thumb.webp', 'width' => 800, 'height' => 600, 'sort_order' => 0]);

        $response = $this->getJson('/api/cms/catalog/detail?site=glosspro', $this->headers());

        $response->assertOk();
        $response->assertJsonPath('data.slug', 'detail');
        $response->assertJsonPath('data.spec_highlights.0.label', 'VLT');
        $response->assertJsonCount(1, 'data.media');
    }

    public function test_show_returns_an_empty_array_for_spec_highlights_when_none_were_entered(): void
    {
        CatalogItem::create(['title' => 'Tanpa Spek', 'slug' => 'tanpa-spek', 'body' => 'x', 'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => true]);

        $response = $this->getJson('/api/cms/catalog/tanpa-spek?site=glosspro', $this->headers());

        $response->assertOk();
        $response->assertJsonPath('data.spec_highlights', []);
    }

    public function test_show_returns_404_for_a_draft_or_wrong_site_or_missing_slug(): void
    {
        CatalogItem::create(['title' => 'Draft', 'slug' => 'draft-item', 'body' => 'x', 'status' => 'draft', 'show_on_glosspro' => true]);

        $this->getJson('/api/cms/catalog/draft-item?site=glosspro', $this->headers())->assertStatus(404);
        $this->getJson('/api/cms/catalog/does-not-exist?site=glosspro', $this->headers())->assertStatus(404);
    }
}
