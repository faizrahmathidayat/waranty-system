<?php

namespace Tests\Feature\Cms;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\CatalogItem;
use App\Models\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->actingAs(Login::factory()->create());
        Storage::fake('public');
    }

    public function test_store_creates_a_catalog_item_with_spec_highlights_and_images(): void
    {
        $response = $this->postJson('/cms/catalog', [
            'title' => 'Produk Baru', 'slug' => 'produk-baru', 'body' => '<p>Isi</p>', 'status' => 'published',
            'show_on_glosspro' => '1',
            'spec_highlights' => [
                ['label' => 'VLT', 'value' => '20%'],
                ['label' => '', 'value' => ''],
            ],
            'images' => [UploadedFile::fake()->image('a.jpg', 1200, 800)],
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $item = CatalogItem::where('slug', 'produk-baru')->firstOrFail();
        $this->assertCount(1, $item->spec_highlights);
        $this->assertSame('VLT', $item->spec_highlights[0]['label']);
        $this->assertCount(1, $item->media);
    }

    public function test_store_saves_an_empty_array_when_no_spec_highlights_are_entered(): void
    {
        $response = $this->postJson('/cms/catalog', [
            'title' => 'Tanpa Spek', 'slug' => 'tanpa-spek', 'body' => '<p>Isi</p>', 'status' => 'draft',
        ]);

        $response->assertOk();
        $item = CatalogItem::where('slug', 'tanpa-spek')->firstOrFail();
        $this->assertSame([], $item->spec_highlights);
    }

    public function test_store_rejects_a_duplicate_slug(): void
    {
        CatalogItem::create(['title' => 'Ada', 'slug' => 'ada', 'body' => 'x', 'status' => 'draft']);

        $this->postJson('/cms/catalog', [
            'title' => 'Baru', 'slug' => 'ada', 'body' => '<p>x</p>', 'status' => 'draft',
        ])->assertStatus(422);
    }

    public function test_update_replaces_spec_highlights(): void
    {
        $item = CatalogItem::create([
            'title' => 'Lama', 'slug' => 'lama', 'body' => 'x', 'status' => 'draft',
            'spec_highlights' => [['label' => 'Lama', 'value' => '1']],
        ]);

        $response = $this->putJson('/cms/catalog/' . $item->id, [
            'title' => 'Lama', 'slug' => 'lama', 'body' => '<p>x</p>', 'status' => 'draft',
            'spec_highlights' => [['label' => 'Baru', 'value' => '2']],
        ]);

        $response->assertOk();
        $this->assertSame('Baru', $item->fresh()->spec_highlights[0]['label']);
    }

    public function test_destroy_deletes_the_item_and_its_media_files(): void
    {
        $item = CatalogItem::create(['title' => 'Hapus', 'slug' => 'hapus', 'body' => 'x', 'status' => 'draft']);
        $media = $item->media()->create(['path' => 'cms/catalog/x/a.webp', 'thumbnail_path' => 'cms/catalog/x/a-thumb.webp', 'width' => 10, 'height' => 10, 'sort_order' => 0]);
        Storage::disk('public')->put($media->path, 'fake');

        $this->deleteJson('/cms/catalog/' . $item->id)->assertOk()->assertJson(['success' => true]);

        $this->assertNull(CatalogItem::find($item->id));
        Storage::disk('public')->assertMissing($media->path);
    }
}
