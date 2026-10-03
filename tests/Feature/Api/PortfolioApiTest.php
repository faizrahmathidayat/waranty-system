<?php

namespace Tests\Feature\Api;

use App\Models\PortfolioItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioApiTest extends TestCase
{
    use RefreshDatabase;

    private function headers(): array
    {
        return ['X-API-Key' => config('services.cms.api_key')];
    }

    public function test_list_requires_api_key(): void
    {
        $this->getJson('/api/cms/portfolio?site=glosspro')->assertStatus(401);
    }

    public function test_list_requires_a_valid_site_parameter(): void
    {
        $this->getJson('/api/cms/portfolio?site=nope', $this->headers())->assertStatus(400);
    }

    public function test_list_returns_only_published_items_for_the_requested_site(): void
    {
        PortfolioItem::create(['title' => 'Glosspro Live', 'slug' => 'glosspro-live', 'body' => 'x', 'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => true]);
        PortfolioItem::create(['title' => 'Lexent Only', 'slug' => 'lexent-only', 'body' => 'x', 'status' => 'published', 'published_at' => now(), 'show_on_lexent' => true]);
        PortfolioItem::create(['title' => 'Draft', 'slug' => 'draft', 'body' => 'x', 'status' => 'draft', 'show_on_glosspro' => true]);

        $response = $this->getJson('/api/cms/portfolio?site=glosspro', $this->headers());

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.slug', 'glosspro-live');
        $response->assertJsonStructure(['data' => [['slug', 'title', 'excerpt', 'category', 'published_at', 'cover']], 'meta' => ['current_page', 'last_page', 'per_page', 'total']]);
    }

    private function lexentItem(string $slug, ?string $category, string $status = 'published'): PortfolioItem
    {
        return PortfolioItem::create(['title' => $slug, 'slug' => $slug, 'body' => 'x', 'category' => $category, 'status' => $status, 'published_at' => now(), 'show_on_lexent' => true]);
    }

    public function test_list_can_be_filtered_by_category_ignoring_case_and_spaces(): void
    {
        $this->lexentItem('ppf-1', 'PPF');
        $this->lexentItem('mobil-1', 'Kaca Film Mobil');
        $this->lexentItem('tanpa-kategori', null);

        $this->getJson('/api/cms/portfolio?site=lexent&category=ppf', $this->headers())
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', 'ppf-1');
        $this->getJson('/api/cms/portfolio?site=lexent&category=' . urlencode('  kaca film mobil '), $this->headers())
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', 'mobil-1');
        $this->getJson('/api/cms/portfolio?site=lexent&category=Tidak+Ada', $this->headers())
            ->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/cms/portfolio?site=lexent', $this->headers())
            ->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_categories_lists_distinct_published_categories_for_the_site(): void
    {
        $this->lexentItem('a', 'Kaca Film Mobil');
        $this->lexentItem('b', 'kaca film mobil');
        $this->lexentItem('c', 'PPF');
        $this->lexentItem('d', 'Rahasia', 'draft');
        $this->lexentItem('e', null);
        $this->lexentItem('f', '   ');
        PortfolioItem::create(['title' => 'g', 'slug' => 'g', 'body' => 'x', 'category' => 'Detailing', 'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => true]);

        $this->getJson('/api/cms/portfolio-categories?site=lexent', $this->headers())
            ->assertOk()->assertExactJson(['data' => ['Kaca Film Mobil', 'PPF']]);
        $this->getJson('/api/cms/portfolio-categories?site=glosspro', $this->headers())
            ->assertOk()->assertExactJson(['data' => ['Detailing']]);
    }

    public function test_categories_requires_api_key_and_valid_site(): void
    {
        $this->getJson('/api/cms/portfolio-categories?site=lexent')->assertStatus(401);
        $this->getJson('/api/cms/portfolio-categories?site=nope', $this->headers())->assertStatus(400);
    }

    public function test_show_returns_body_and_location_and_media(): void
    {
        $item = PortfolioItem::create([
            'title' => 'Detail', 'slug' => 'detail', 'body' => '<p>Isi lengkap</p>', 'status' => 'published',
            'published_at' => now(), 'show_on_glosspro' => true, 'location' => 'Bandung',
        ]);
        $item->media()->create(['path' => 'cms/portfolio/1/a.webp', 'thumbnail_path' => 'cms/portfolio/1/a-thumb.webp', 'width' => 800, 'height' => 600, 'sort_order' => 0]);

        $response = $this->getJson('/api/cms/portfolio/detail?site=glosspro', $this->headers());

        $response->assertOk();
        $response->assertJsonPath('data.slug', 'detail');
        $response->assertJsonPath('data.location', 'Bandung');
        $response->assertJsonCount(1, 'data.media');
    }

    public function test_show_returns_404_for_a_draft_or_wrong_site_or_missing_slug(): void
    {
        PortfolioItem::create(['title' => 'Draft', 'slug' => 'draft-item', 'body' => 'x', 'status' => 'draft', 'show_on_glosspro' => true]);

        $this->getJson('/api/cms/portfolio/draft-item?site=glosspro', $this->headers())->assertStatus(404);
        $this->getJson('/api/cms/portfolio/does-not-exist?site=glosspro', $this->headers())->assertStatus(404);
    }
}
