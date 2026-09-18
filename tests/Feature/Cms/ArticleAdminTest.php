<?php

namespace Tests\Feature\Cms;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Article;
use App\Models\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->actingAs(Login::factory()->create());
        Storage::fake('public');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        auth()->logout();

        $this->get('/cms/articles')->assertRedirect('/login');
    }

    public function test_store_creates_an_article_with_uploaded_images(): void
    {
        $response = $this->postJson('/cms/articles', [
            'title' => 'Judul Baru',
            'slug' => 'judul-baru',
            'body' => '<p>Isi</p>',
            'status' => 'published',
            'show_on_glosspro' => '1',
            'images' => [
                UploadedFile::fake()->image('a.jpg', 1200, 800),
                UploadedFile::fake()->image('b.jpg', 1200, 800),
            ],
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $article = Article::where('slug', 'judul-baru')->firstOrFail();
        $this->assertSame('published', $article->status);
        $this->assertNotNull($article->published_at);
        $this->assertCount(2, $article->media);
        Storage::disk('public')->assertExists($article->media->first()->path);
    }

    public function test_store_rejects_a_duplicate_slug(): void
    {
        Article::create(['title' => 'Ada', 'slug' => 'ada', 'body' => 'x', 'status' => 'draft']);

        $this->postJson('/cms/articles', [
            'title' => 'Baru', 'slug' => 'ada', 'body' => '<p>x</p>', 'status' => 'draft',
        ])->assertStatus(422);
    }

    public function test_update_replaces_fields_and_can_add_more_images(): void
    {
        $article = Article::create(['title' => 'Lama', 'slug' => 'lama', 'body' => 'x', 'status' => 'draft']);

        $response = $this->putJson('/cms/articles/' . $article->id, [
            'title' => 'Baru', 'slug' => 'lama', 'body' => '<p>Baru</p>', 'status' => 'draft',
            'images' => [UploadedFile::fake()->image('c.jpg', 900, 600)],
        ]);

        $response->assertOk();
        $this->assertSame('Baru', $article->fresh()->title);
        $this->assertCount(1, $article->fresh()->media);
    }

    public function test_destroy_deletes_the_article_and_its_media_files(): void
    {
        $article = Article::create(['title' => 'Hapus', 'slug' => 'hapus', 'body' => 'x', 'status' => 'draft']);
        $media = $article->media()->create(['path' => 'cms/articles/x/a.webp', 'thumbnail_path' => 'cms/articles/x/a-thumb.webp', 'width' => 10, 'height' => 10, 'sort_order' => 0]);
        Storage::disk('public')->put($media->path, 'fake');
        Storage::disk('public')->put($media->thumbnail_path, 'fake');

        $this->deleteJson('/cms/articles/' . $article->id)->assertOk()->assertJson(['success' => true]);

        $this->assertNull(Article::find($article->id));
        Storage::disk('public')->assertMissing($media->path);
    }

    public function test_media_destroy_removes_a_single_image_without_deleting_the_article(): void
    {
        $article = Article::create(['title' => 'Ada Gambar', 'slug' => 'ada-gambar', 'body' => 'x', 'status' => 'draft']);
        $media = $article->media()->create(['path' => 'cms/articles/x/a.webp', 'thumbnail_path' => 'cms/articles/x/a-thumb.webp', 'width' => 10, 'height' => 10, 'sort_order' => 0]);
        Storage::disk('public')->put($media->path, 'fake');

        $this->deleteJson('/cms/articles/media/' . $media->id)->assertOk();

        $this->assertNotNull(Article::find($article->id));
        $this->assertNull(\App\Models\CmsMedia::find($media->id));
    }
}
