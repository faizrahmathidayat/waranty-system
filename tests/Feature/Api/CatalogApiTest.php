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
