<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\CmsMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_relation_uses_the_short_morph_map_alias(): void
    {
        $article = Article::create([
            'title' => 'Judul Uji',
            'slug' => 'judul-uji',
            'body' => '<p>Isi</p>',
            'status' => 'published',
            'published_at' => now(),
            'show_on_glosspro' => true,
        ]);

        $media = $article->media()->create([
            'path' => 'cms/articles/1/a.webp',
            'thumbnail_path' => 'cms/articles/1/a-thumb.webp',
            'width' => 1920,
            'height' => 1080,
            'sort_order' => 0,
        ]);

        $this->assertSame('article', $media->fresh()->mediable_type);
        $this->assertTrue($article->media->first()->is($media));
        $this->assertSame($article->id, $media->mediable->id);
    }

    public function test_published_scope_excludes_drafts_and_future_dated_posts(): void
    {
        Article::create(['title' => 'Draft', 'slug' => 'draft', 'body' => 'x', 'status' => 'draft', 'show_on_glosspro' => true]);
        Article::create(['title' => 'Future', 'slug' => 'future', 'body' => 'x', 'status' => 'published', 'published_at' => now()->addDay(), 'show_on_glosspro' => true]);
        $live = Article::create(['title' => 'Live', 'slug' => 'live', 'body' => 'x', 'status' => 'published', 'published_at' => now()->subHour(), 'show_on_glosspro' => true]);

        $result = Article::published()->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($live));
    }

    public function test_for_site_scope_filters_by_the_matching_boolean_column(): void
    {
        $glosspro = Article::create(['title' => 'A', 'slug' => 'a', 'body' => 'x', 'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => true, 'show_on_lexent' => false]);
        Article::create(['title' => 'B', 'slug' => 'b', 'body' => 'x', 'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => false, 'show_on_lexent' => true]);

        $result = Article::published()->forSite('glosspro')->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($glosspro));
    }
}
