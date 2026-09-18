<?php

namespace Tests\Feature\Cms;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Login;
use App\Models\PortfolioItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortfolioAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->actingAs(Login::factory()->create());
        Storage::fake('public');
    }

    public function test_store_creates_a_portfolio_item_with_location_and_images(): void
    {
        $response = $this->postJson('/cms/portfolio', [
            'title' => 'Proyek Baru', 'slug' => 'proyek-baru', 'body' => '<p>Isi</p>', 'status' => 'published',
            'show_on_glosspro' => '1', 'location' => 'Surabaya',
            'images' => [UploadedFile::fake()->image('a.jpg', 1200, 800)],
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $item = PortfolioItem::where('slug', 'proyek-baru')->firstOrFail();
        $this->assertSame('Surabaya', $item->location);
        $this->assertCount(1, $item->media);
    }

    public function test_store_allows_a_blank_location(): void
    {
        $response = $this->postJson('/cms/portfolio', [
            'title' => 'Tanpa Lokasi', 'slug' => 'tanpa-lokasi', 'body' => '<p>Isi</p>', 'status' => 'draft',
        ]);

        $response->assertOk();
        $item = PortfolioItem::where('slug', 'tanpa-lokasi')->firstOrFail();
        $this->assertNull($item->location);
    }

    public function test_store_rejects_a_duplicate_slug(): void
    {
        PortfolioItem::create(['title' => 'Ada', 'slug' => 'ada', 'body' => 'x', 'status' => 'draft']);

        $this->postJson('/cms/portfolio', [
            'title' => 'Baru', 'slug' => 'ada', 'body' => '<p>x</p>', 'status' => 'draft',
        ])->assertStatus(422);
    }

    public function test_update_replaces_location(): void
    {
        $item = PortfolioItem::create(['title' => 'Lama', 'slug' => 'lama', 'body' => 'x', 'status' => 'draft', 'location' => 'Lama']);

        $response = $this->putJson('/cms/portfolio/' . $item->id, [
            'title' => 'Lama', 'slug' => 'lama', 'body' => '<p>x</p>', 'status' => 'draft', 'location' => 'Baru',
        ]);

        $response->assertOk();
        $this->assertSame('Baru', $item->fresh()->location);
    }

    public function test_destroy_deletes_the_item_and_its_media_files(): void
    {
        $item = PortfolioItem::create(['title' => 'Hapus', 'slug' => 'hapus', 'body' => 'x', 'status' => 'draft']);
        $media = $item->media()->create(['path' => 'cms/portfolio/x/a.webp', 'thumbnail_path' => 'cms/portfolio/x/a-thumb.webp', 'width' => 10, 'height' => 10, 'sort_order' => 0]);
        Storage::disk('public')->put($media->path, 'fake');

        $this->deleteJson('/cms/portfolio/' . $item->id)->assertOk()->assertJson(['success' => true]);

        $this->assertNull(PortfolioItem::find($item->id));
        Storage::disk('public')->assertMissing($media->path);
    }
}
