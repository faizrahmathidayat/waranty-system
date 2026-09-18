<?php

namespace Tests\Feature\Api;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleApiTest extends TestCase
{
    use RefreshDatabase;

    private function headers(): array
    {
        return ['X-API-Key' => config('services.cms.api_key')];
    }

    public function test_list_requires_api_key(): void
    {
        $this->getJson('/api/cms/articles?site=glosspro')->assertStatus(401);
    }

    public function test_list_requires_a_valid_site_parameter(): void
    {
        $this->getJson('/api/cms/articles?site=nope', $this->headers())->assertStatus(400);
        $this->getJson('/api/cms/articles', $this->headers())->assertStatus(400);
    }

    public function test_list_returns_only_published_items_for_the_requested_site(): void
    {
        Article::create(['title' => 'Glosspro Live', 'slug' => 'glosspro-live', 'body' => 'x', 'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => true]);
        Article::create(['title' => 'Lexent Only', 'slug' => 'lexent-only', 'body' => 'x', 'status' => 'published', 'published_at' => now(), 'show_on_lexent' => true]);
        Article::create(['title' => 'Draft', 'slug' => 'draft', 'body' => 'x', 'status' => 'draft', 'show_on_glosspro' => true]);

        $response = $this->getJson('/api/cms/articles?site=glosspro', $this->headers());

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.slug', 'glosspro-live');
        $response->assertJsonStructure(['data' => [['slug', 'title', 'excerpt', 'category', 'published_at', 'cover']], 'meta' => ['current_page', 'last_page', 'per_page', 'total']]);
    }

    public function test_excerpt_falls_back_to_a_truncated_body_when_blank(): void
    {
        Article::create([
            'title' => 'No Excerpt', 'slug' => 'no-excerpt', 'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => true,
            'body' => '<p>' . str_repeat('kata ', 60) . '</p>',
        ]);

        $response = $this->getJson('/api/cms/articles?site=glosspro', $this->headers());

        $response->assertOk();
        $this->assertNotEmpty($response->json('data.0.excerpt'));
        $this->assertLessThanOrEqual(165, strlen($response->json('data.0.excerpt')));
        $this->assertStringNotContainsString('<p>', $response->json('data.0.excerpt'));
    }

    public function test_show_returns_body_and_media_for_a_published_matching_article(): void
    {
        $article = Article::create(['title' => 'Detail', 'slug' => 'detail', 'body' => '<p>Isi lengkap</p>', 'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => true]);
        $article->media()->create(['path' => 'cms/articles/1/a.webp', 'thumbnail_path' => 'cms/articles/1/a-thumb.webp', 'width' => 800, 'height' => 600, 'sort_order' => 0]);

        $response = $this->getJson('/api/cms/articles/detail?site=glosspro', $this->headers());

        $response->assertOk();
        $response->assertJsonPath('data.slug', 'detail');
        $response->assertJsonPath('data.body', '<p>Isi lengkap</p>');
        $response->assertJsonCount(1, 'data.media');
    }

    public function test_show_returns_404_for_a_draft_or_wrong_site_or_missing_slug(): void
    {
        Article::create(['title' => 'Draft', 'slug' => 'draft-item', 'body' => 'x', 'status' => 'draft', 'show_on_glosspro' => true]);
        Article::create(['title' => 'Lexent Only', 'slug' => 'lexent-item', 'body' => 'x', 'status' => 'published', 'published_at' => now(), 'show_on_lexent' => true]);

        $this->getJson('/api/cms/articles/draft-item?site=glosspro', $this->headers())->assertStatus(404);
        $this->getJson('/api/cms/articles/lexent-item?site=glosspro', $this->headers())->assertStatus(404);
        $this->getJson('/api/cms/articles/does-not-exist?site=glosspro', $this->headers())->assertStatus(404);
    }
}
