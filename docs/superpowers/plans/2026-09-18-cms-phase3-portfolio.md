# CMS Phase 3 — Portofolio (dashboard) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship the third and final CMS content type — Portofolio — end-to-end in the `dashboard` app: admin CRUD with multi-image upload and a `location` field, plus a public JSON API that `compro-1`/`compro-2` will consume in a later, separate plan.

**Architecture:** New `cms_portfolio_items` table, same shape as `cms_articles` (Phase 1) plus one extra nullable `location` string column. Reuses every piece of shared infrastructure Phase 1/2 already built: the polymorphic `cms_media` table, `Relation::morphMap()` (one new entry, `'portfolio' => PortfolioItem::class`), the `cms.api_key` middleware, the `ImageProcessor` service, and the admin layout's `@stack` hooks. Admin CRUD lives at `/cms/portfolio/*`. Public API lives at `/api/cms/portfolio/*`.

**Scope decision (confirmed with user 2026-09-18):** This plan is **dashboard-only** — same scoping choice as Phase 1 and Phase 2. It does NOT touch `compro-1` or `compro-2`. In particular, it does **not** rewire `compro-1`'s existing `/portfolio` route (currently hardcoded in `PageController::portfolioItems()`) to this new API, and does not add a new `/portfolio` route to `compro-2` — both of those are consumer-side changes to an already-shipped page and are deferred, together with the still-outstanding Artikel and Katalog consumer integrations, to one later "CMS consumer integration" plan per site.

**Tech Stack:** Laravel 8.83 / PHP 7.4.33 (PHP-7.4-pinned — see Global Constraints), `intervention/image` ^2.7 and `yajra/laravel-datatables-oracle` ^9.0 (already in use), Quill.js 1.3.7 via CDN (already wired into the admin layout).

**Spec:** `docs/superpowers/specs/2026-09-18-cms-design.md` (see "Fase 3 — Portofolio" and the `cms_portfolio_items` table definition)

**Prior phases:** `docs/superpowers/plans/2026-09-18-cms-phase1-articles.md` and `...-cms-phase2-catalog.md` — this plan mirrors their Article/CatalogItem shape almost exactly; `location` (a plain nullable string) is simpler to handle than Katalog's repeatable `spec_highlights` (a JSON array), so the admin form needs only one extra text input, not a repeatable-row editor.

## Global Constraints

- **PHP 7.4 syntax only** — no constructor property promotion, no `match()`, no nullsafe `?->`, no native `enum`. Typed properties, arrow functions, and `??=` are fine.
- No `env()` calls outside `config/services.php` — this plan adds no new config keys.
- Do not touch the Warranty/Order/Invoice/Product(SKU) domain.
- Do not touch `compro-1` or `compro-2` — see the Scope decision above.
- Follow the existing AdminLTE/jQuery/Toastr/SweetAlert2/DataTables admin conventions — **except** the form itself: Portofolio's form (WYSIWYG body + multi-image upload) gets real pages (`/cms/portfolio/create`, `/cms/portfolio/{portfolioItem}/edit`), same deviation already established for Artikel and Katalog.
- Auth: routes under `/cms/portfolio` are protected automatically by the existing global `EnsureSessionAuthenticated` middleware — do not add a second auth check.
- Tests use the existing PHPUnit setup (`tests/Feature`, `RefreshDatabase`, `Login::factory()->create()` + `actingAs()`, `$this->withoutMiddleware(VerifyCsrfToken::class)` for POSTs). Run with `php artisan test`.
- `created_by` on `cms_portfolio_items` is a plain nullable `integer`, same reasoning as the other two CMS tables.
- No `SoftDeletes` — same reasoning as Phase 1/2.

---

### Task 1: Database schema — `cms_portfolio_items`

**Files:**
- Create: `database/migrations/2026_09_18_000004_create_cms_portfolio_items_table.php`
- Test: `tests/Feature/CmsPortfolioItemSchemaTest.php`

**Interfaces:**
- Produces: table `cms_portfolio_items` — every column `cms_articles` has, plus `location` string nullable.

- [ ] **Step 1: Write the migration**

`database/migrations/2026_09_18_000004_create_cms_portfolio_items_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_portfolio_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt', 500)->nullable();
            $table->longText('body');
            $table->string('category')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->boolean('show_on_glosspro')->default(false);
            $table->boolean('show_on_lexent')->default(false);
            $table->string('location')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_portfolio_items');
    }
};
```

- [ ] **Step 2: Write a schema smoke test**

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CmsPortfolioItemSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_cms_portfolio_items_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('cms_portfolio_items'));
        $this->assertTrue(Schema::hasColumns('cms_portfolio_items', [
            'title', 'slug', 'excerpt', 'body', 'category', 'status',
            'published_at', 'show_on_glosspro', 'show_on_lexent', 'location', 'created_by',
        ]));
    }
}
```

- [ ] **Step 3: Run the test to verify it passes**

Run: `php artisan test --filter=CmsPortfolioItemSchemaTest`
Expected: PASS immediately (RefreshDatabase runs all migrations first). If it errors, the migration has a typo — fix and retry.

- [ ] **Step 4: Run the real migration against the local dev database**

Run: `php artisan migrate`
Expected: `Migrating: 2026_09_18_000004_create_cms_portfolio_items_table` / `Migrated:`, no errors.

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_09_18_000004_create_cms_portfolio_items_table.php tests/Feature/CmsPortfolioItemSchemaTest.php
git commit -m "Add cms_portfolio_items table"
```

---

### Task 2: Model — `PortfolioItem`, morph map entry

**Files:**
- Create: `app/Models/PortfolioItem.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Test: `tests/Unit/PortfolioItemModelTest.php`

**Interfaces:**
- Consumes: `cms_portfolio_items` table (Task 1), `CmsMedia` model.
- Produces: `PortfolioItem::published()` scope, `PortfolioItem::forSite(string $site)` scope, `PortfolioItem->media()` relation. This is the last content type — the `morphMap()` array is now complete at three entries.

- [ ] **Step 1: Write the failing test**

```php
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
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --filter=PortfolioItemModelTest`
Expected: FAIL — `Class "App\Models\PortfolioItem" not found`.

- [ ] **Step 3: Write `app/Models/PortfolioItem.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioItem extends Model
{
    use HasFactory;

    protected $table = 'cms_portfolio_items';

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'category',
        'status', 'published_at', 'show_on_glosspro', 'show_on_lexent',
        'location', 'created_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'show_on_glosspro' => 'boolean',
        'show_on_lexent' => 'boolean',
    ];

    public function media()
    {
        return $this->morphMany(CmsMedia::class, 'mediable')->orderBy('sort_order');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->where('published_at', '<=', now());
    }

    public function scopeForSite($query, string $site)
    {
        return $query->where('show_on_' . $site, true);
    }
}
```

- [ ] **Step 4: Add the morph map entry in `app/Providers/AppServiceProvider.php`**

The `boot()` method currently reads (from Phase 1/2):
```php
        Relation::morphMap([
            'article' => Article::class,
            'catalog' => CatalogItem::class,
        ]);
```
Change it to:
```php
        Relation::morphMap([
            'article' => Article::class,
            'catalog' => CatalogItem::class,
            'portfolio' => PortfolioItem::class,
        ]);
```
Add `use App\Models\PortfolioItem;` alongside the existing `use App\Models\Article;` / `use App\Models\CatalogItem;` lines.

- [ ] **Step 5: Run the test to verify it passes**

Run: `php artisan test --filter=PortfolioItemModelTest`
Expected: PASS (3 tests).

- [ ] **Step 6: Commit**

```bash
git add app/Models/PortfolioItem.php app/Providers/AppServiceProvider.php tests/Unit/PortfolioItemModelTest.php
git commit -m "Add PortfolioItem model and register it in the CMS morph map"
```

---

### Task 3: Public API — `GET /api/cms/portfolio` and `GET /api/cms/portfolio/{slug}`

**Files:**
- Create: `app/Http/Controllers/Api/PortfolioController.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/Api/PortfolioApiTest.php`

**Interfaces:**
- Consumes: `PortfolioItem::published()`, `PortfolioItem::forSite()` (Task 2), `cms.api_key` middleware.
- Produces: list item shape `{slug, title, excerpt, category, published_at, cover}` — identical to Artikel/Katalog's. Detail shape: list item shape + `{body, location, media: [...]}`. `location` is `null` when not set (unlike Katalog's `spec_highlights`, there's no "empty array" convention needed for a plain nullable string).

- [ ] **Step 1: Write the failing tests**

```php
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
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --filter=PortfolioApiTest`
Expected: FAIL — routes don't exist yet.

- [ ] **Step 3: Write `app/Http/Controllers/Api/PortfolioController.php`**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CmsMedia;
use App\Models\PortfolioItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PortfolioController extends Controller
{
    private const ALLOWED_SITES = ['glosspro', 'lexent'];
    private const DEFAULT_PER_PAGE = 9;
    private const MAX_PER_PAGE = 30;

    public function index(Request $request)
    {
        $site = $this->validateSite($request);
        if ($site === null) {
            return response()->json(['message' => 'Parameter site wajib diisi dengan glosspro atau lexent.'], 400);
        }

        $perPage = (int) $request->query('per_page', self::DEFAULT_PER_PAGE);
        $perPage = $perPage > 0 ? min($perPage, self::MAX_PER_PAGE) : self::DEFAULT_PER_PAGE;

        $items = PortfolioItem::published()
            ->forSite($site)
            ->with('media')
            ->orderByDesc('published_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $items->getCollection()->map(function (PortfolioItem $item) {
                return $this->summarize($item);
            })->all(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $site = $this->validateSite($request);
        if ($site === null) {
            return response()->json(['message' => 'Parameter site wajib diisi dengan glosspro atau lexent.'], 400);
        }

        $item = PortfolioItem::published()->forSite($site)->with('media')->where('slug', $slug)->first();

        if (!$item) {
            return response()->json(['message' => 'Item portofolio tidak ditemukan.'], 404);
        }

        $payload = $this->summarize($item);
        $payload['body'] = $item->body;
        $payload['location'] = $item->location;
        $payload['media'] = $item->media->map(function (CmsMedia $media) {
            return $this->mediaPayload($media);
        })->all();

        return response()->json(['data' => $payload]);
    }

    private function validateSite(Request $request): ?string
    {
        $site = $request->query('site');
        return in_array($site, self::ALLOWED_SITES, true) ? $site : null;
    }

    private function summarize(PortfolioItem $item): array
    {
        $cover = $item->media->first();
        $excerpt = $item->excerpt ?: Str::limit(trim(strip_tags($item->body)), 160);

        return [
            'slug' => $item->slug,
            'title' => $item->title,
            'excerpt' => $excerpt,
            'category' => $item->category,
            'published_at' => optional($item->published_at)->toIso8601String(),
            'cover' => $cover ? $this->mediaPayload($cover) : null,
        ];
    }

    private function mediaPayload(CmsMedia $media): array
    {
        return [
            'url' => $media->url,
            'thumbnail_url' => $media->thumbnail_url,
            'width' => $media->width,
            'height' => $media->height,
            'alt_text' => $media->alt_text,
        ];
    }
}
```

- [ ] **Step 4: Add the routes**

In `routes/api.php`, add the import alongside the existing `use App\Http\Controllers\Api\CatalogController;` line, and add two routes inside the existing `cms.api_key` group:
```php
use App\Http\Controllers\Api\PortfolioController;
```
```php
Route::middleware('cms.api_key')->prefix('cms')->group(function () {
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{slug}', [ArticleController::class, 'show']);
    Route::get('/catalog', [CatalogController::class, 'index']);
    Route::get('/catalog/{slug}', [CatalogController::class, 'show']);
    Route::get('/portfolio', [PortfolioController::class, 'index']);
    Route::get('/portfolio/{slug}', [PortfolioController::class, 'show']);
});
```

- [ ] **Step 5: Run the tests to verify they pass**

Run: `php artisan test --filter=PortfolioApiTest`
Expected: PASS (5 tests).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Api/PortfolioController.php routes/api.php tests/Feature/Api/PortfolioApiTest.php
git commit -m "Add public Portofolio API (list + detail)"
```

---

### Task 4: Admin CRUD controller — create/edit/delete + image upload

**Files:**
- Create: `app/Http/Controllers/Cms/PortfolioController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Cms/PortfolioAdminTest.php`

**Interfaces:**
- Consumes: `PortfolioItem`, `CmsMedia` (Task 2), `ImageProcessor::process()`.
- Produces: routes `cms.portfolio.index|data|show|store|update|destroy|media.destroy`. `store`/`update` accept `images[]` (multipart file array) and a plain `location` text field.

- [ ] **Step 1: Write the failing tests**

```php
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
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --filter=PortfolioAdminTest`
Expected: FAIL — routes don't exist yet.

- [ ] **Step 3: Write `app/Http/Controllers/Cms/PortfolioController.php`**

```php
<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\CmsMedia;
use App\Models\PortfolioItem;
use App\Services\ImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class PortfolioController extends Controller
{
    private ImageProcessor $imageProcessor;

    public function __construct(ImageProcessor $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
    }

    public function index()
    {
        return view('cms.portfolio.index', [
            'title' => 'Portofolio',
            'navbar' => 'CMS - Portofolio',
        ]);
    }

    public function data()
    {
        return DataTables::of(PortfolioItem::query()->orderByDesc('id'))
            ->addColumn('cover', function (PortfolioItem $item) {
                $cover = $item->media()->first();
                return $cover ? $cover->thumbnail_url : null;
            })
            ->addColumn('sites', function (PortfolioItem $item) {
                $sites = [];
                if ($item->show_on_glosspro) {
                    $sites[] = 'GlossPro';
                }
                if ($item->show_on_lexent) {
                    $sites[] = 'LEXENT';
                }
                return implode(', ', $sites) ?: '-';
            })
            ->toJson();
    }

    public function show(PortfolioItem $portfolioItem)
    {
        $portfolioItem->load('media');
        return response()->json($portfolioItem);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = auth()->id();

        $item = PortfolioItem::create($data);
        $this->storeUploadedImages($item, $request);

        return response()->json(['success' => true, 'id' => $item->id]);
    }

    public function update(Request $request, PortfolioItem $portfolioItem)
    {
        $data = $this->validated($request, $portfolioItem->id);

        $portfolioItem->update($data);
        $this->storeUploadedImages($portfolioItem, $request);

        return response()->json(['success' => true]);
    }

    public function destroy(PortfolioItem $portfolioItem)
    {
        foreach ($portfolioItem->media as $media) {
            $this->deleteMediaFiles($media);
        }

        $portfolioItem->delete();

        return response()->json(['success' => true]);
    }

    public function destroyMedia(CmsMedia $media)
    {
        $this->deleteMediaFiles($media);

        return response()->json(['success' => true]);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = 'required|string|max:255|unique:cms_portfolio_items,slug';
        if ($ignoreId !== null) {
            $slugRule .= ',' . $ignoreId;
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => $slugRule,
            'excerpt' => 'nullable|string|max:500',
            'body' => 'required|string',
            'category' => 'nullable|string|max:100',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'location' => 'nullable|string|max:255',
        ]);

        $data['show_on_glosspro'] = $request->boolean('show_on_glosspro');
        $data['show_on_lexent'] = $request->boolean('show_on_lexent');

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    private function storeUploadedImages(PortfolioItem $item, Request $request): void
    {
        if (!$request->hasFile('images')) {
            return;
        }

        $nextOrder = (int) $item->media()->max('sort_order') + 1;

        foreach ($request->file('images') as $file) {
            $processed = $this->imageProcessor->process($file, 'cms/portfolio/' . $item->id);

            $item->media()->create(array_merge($processed, [
                'sort_order' => $nextOrder,
            ]));

            $nextOrder++;
        }
    }

    private function deleteMediaFiles(CmsMedia $media): void
    {
        Storage::disk('public')->delete([$media->path, $media->thumbnail_path]);
        $media->delete();
    }
}
```

- [ ] **Step 4: Add the routes**

In `routes/web.php`, add the import alongside the existing `use App\Http\Controllers\Cms\CatalogController;` line:
```php
use App\Http\Controllers\Cms\PortfolioController;
```
And a new route group, right after the existing `cms/catalog` group:
```php
// CMS - Portofolio
Route::prefix('cms/portfolio')->name('cms.portfolio.')->group(function () {
    Route::get('/', [PortfolioController::class, 'index'])->name('index');
    Route::get('/data', [PortfolioController::class, 'data'])->name('data');
    Route::get('/create', [PortfolioController::class, 'index'])->name('create');
    Route::get('/{portfolioItem}/edit', [PortfolioController::class, 'index'])->name('edit');
    Route::get('/{portfolioItem}', [PortfolioController::class, 'show'])->name('show');
    Route::post('/', [PortfolioController::class, 'store'])->name('store');
    Route::put('/{portfolioItem}', [PortfolioController::class, 'update'])->name('update');
    Route::delete('/{portfolioItem}', [PortfolioController::class, 'destroy'])->name('destroy');
    Route::delete('/media/{media}', [PortfolioController::class, 'destroyMedia'])->name('media.destroy');
});
```
Same ordering rule as Phase 1/2: `/data`, `/create`, `/{portfolioItem}/edit` must be registered before the generic `/{portfolioItem}` GET.

- [ ] **Step 5: Run the tests to verify they pass**

Run: `php artisan test --filter=PortfolioAdminTest`
Expected: PASS (5 tests).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Cms/PortfolioController.php routes/web.php tests/Feature/Cms/PortfolioAdminTest.php
git commit -m "Add Portofolio admin CRUD with location field and multi-image upload"
```

---

### Task 5: Admin views — Portofolio list + create/edit form + sidebar link

**Files:**
- Create: `resources/views/cms/portfolio/index.blade.php`
- Create: `public/js/cms_portfolio_function.js`
- Modify: `resources/views/layout/sidebar.blade.php`

**Interfaces:**
- Consumes: routes from Task 4, `@stack` hooks.
- Produces: a working browser UI. No automated test (same reasoning as Phase 1/2's view tasks); verification is manual, listed as the final step.

- [ ] **Step 1: Write `resources/views/cms/portfolio/index.blade.php`**

```blade
@extends('layout.layout')

@section('title', $title)

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
<style>
    .cms-gallery { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
    .cms-gallery-item { position: relative; width: 120px; }
    .cms-gallery-item img { width: 120px; height: 90px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6; }
    .cms-gallery-item .remove-image { position: absolute; top: -8px; right: -8px; background: #dc3545; color: #fff; border-radius: 50%; width: 22px; height: 22px; line-height: 22px; text-align: center; cursor: pointer; font-size: 12px; }
    #quill-body-editor { background: #fff; min-height: 260px; }
    .cms-cover-thumb { width: 50px; height: 38px; object-fit: cover; border-radius: 3px; }
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1>Portofolio</h1></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        {{-- List view --}}
        <div id="cms-portfolio-list-panel" class="card">
            <div class="card-header">
                <a href="{{ route('cms.portfolio.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Proyek</a>
            </div>
            <div class="card-body">
                <table id="table-portfolio" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>Cover</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Situs</th>
                            <th>Tanggal Terbit</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        {{-- Create/Edit form --}}
        <div id="cms-portfolio-form-panel" class="card" style="display:none;">
            <div class="card-header">
                <h3 class="card-title" id="cms-portfolio-form-title">Tambah Proyek</h3>
            </div>
            <form id="form-portfolio">
                @csrf
                <input type="hidden" id="portfolio_id" name="portfolio_id">
                <div class="card-body">
                    <div class="form-group">
                        <label>Judul</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Slug</label>
                        <input type="text" class="form-control" id="slug" name="slug" required>
                        <small class="form-text text-muted">Terisi otomatis dari judul, bisa diubah manual.</small>
                    </div>
                    <div class="form-group">
                        <label>Ringkasan (opsional)</label>
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="2" maxlength="500"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Isi</label>
                        <div id="quill-body-editor"></div>
                        <textarea name="body" id="body" style="display:none;"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Kategori (opsional)</label>
                        <input type="text" class="form-control" id="category" name="category">
                    </div>
                    <div class="form-group">
                        <label>Lokasi (opsional)</label>
                        <input type="text" class="form-control" id="location" name="location" placeholder="mis. Jakarta">
                    </div>
                    <div class="form-group">
                        <label>Tampilkan di situs</label><br>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="show_on_glosspro" name="show_on_glosspro" value="1">
                            <label class="custom-control-label" for="show_on_glosspro">GlossPro (compro-1)</label>
                        </div>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" class="custom-control-input" id="show_on_lexent" name="show_on_lexent" value="1">
                            <label class="custom-control-label" for="show_on_lexent">LEXENT (compro-2)</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Gambar</label>
                        <div id="cms-existing-gallery" class="cms-gallery"></div>
                        <input type="file" class="form-control-file mt-2" id="images" name="images[]" multiple accept="image/*">
                        <small class="form-text text-muted">Bisa pilih lebih dari satu gambar. Otomatis diubah ke WebP dan dikompres saat disimpan.</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    <a href="{{ route('cms.portfolio.index') }}" class="btn btn-default">Batal</a>
                </div>
            </form>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
    window.CMS_PORTFOLIO_ROUTES = {
        data: "{{ route('cms.portfolio.data') }}",
        store: "{{ route('cms.portfolio.store') }}",
        index: "{{ route('cms.portfolio.index') }}",
        showTemplate: "{{ route('cms.portfolio.show', ['portfolioItem' => '__ID__']) }}",
        updateTemplate: "{{ route('cms.portfolio.update', ['portfolioItem' => '__ID__']) }}",
        destroyTemplate: "{{ route('cms.portfolio.destroy', ['portfolioItem' => '__ID__']) }}",
        mediaDestroyTemplate: "{{ route('cms.portfolio.media.destroy', ['media' => '__ID__']) }}",
        editTemplate: "{{ route('cms.portfolio.edit', ['portfolioItem' => '__ID__']) }}",
        createUrl: "{{ route('cms.portfolio.create') }}",
    };
    window.CMS_PORTFOLIO_EDIT_ID = @json(request()->routeIs('cms.portfolio.edit') ? request()->route('portfolioItem') : null);
    window.CMS_PORTFOLIO_IS_CREATE = @json(request()->routeIs('cms.portfolio.create'));
</script>
<script src="js/cms_portfolio_function.js?v={{ filemtime(public_path('js/cms_portfolio_function.js')) }}"></script>
@endpush
```

- [ ] **Step 2: Write `public/js/cms_portfolio_function.js`**

```javascript
$(function () {
    var routes = window.CMS_PORTFOLIO_ROUTES;
    var isFormMode = window.CMS_PORTFOLIO_IS_CREATE || !!window.CMS_PORTFOLIO_EDIT_ID;
    var quill = null;

    if (isFormMode) {
        $('#cms-portfolio-list-panel').hide();
        $('#cms-portfolio-form-panel').show();
        quill = new Quill('#quill-body-editor', { theme: 'snow' });
    } else {
        initDataTable();
    }

    if (window.CMS_PORTFOLIO_EDIT_ID) {
        loadItemForEdit(window.CMS_PORTFOLIO_EDIT_ID);
    }

    $('#title').on('input', function () {
        if (!window.CMS_PORTFOLIO_EDIT_ID) {
            $('#slug').val(slugify($(this).val()));
        }
    });

    $('#form-portfolio').on('submit', function (e) {
        e.preventDefault();
        $('#body').val(quill.root.innerHTML);

        var formData = new FormData(this);
        var id = $('#portfolio_id').val();
        var url = id ? routes.updateTemplate.replace('__ID__', id) : routes.store;

        if (id) {
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                toastr.success('Proyek berhasil disimpan.');
                window.location.href = routes.index;
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors || {};
                    var firstMessage = Object.values(errors)[0];
                    toastr.error(firstMessage ? firstMessage[0] : 'Data tidak valid.');
                } else {
                    toastr.error('Gagal menyimpan proyek.');
                }
            },
        });
    });

    $(document).on('click', '.remove-existing-image', function () {
        var mediaId = $(this).data('id');
        var $item = $(this).closest('.cms-gallery-item');

        $.ajax({
            url: routes.mediaDestroyTemplate.replace('__ID__', mediaId),
            method: 'DELETE',
            success: function () {
                $item.remove();
                toastr.success('Gambar dihapus.');
            },
            error: function () {
                toastr.error('Gagal menghapus gambar.');
            },
        });
    });

    function initDataTable() {
        $('#table-portfolio').DataTable({
            processing: true,
            serverSide: true,
            ajax: routes.data,
            columns: [
                { data: 'cover', orderable: false, render: function (url) { return url ? '<img src="' + url + '" class="cms-cover-thumb">' : '-'; } },
                { data: 'title' },
                { data: 'category', defaultContent: '-' },
                { data: 'status' },
                { data: 'sites' },
                { data: 'published_at', defaultContent: '-' },
                {
                    data: 'id', orderable: false,
                    render: function (id) {
                        var editUrl = routes.editTemplate.replace('__ID__', id);
                        return '<a href="' + editUrl + '" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a> ' +
                            '<button type="button" class="btn btn-sm btn-danger btn-delete-item" data-id="' + id + '"><i class="fas fa-trash"></i></button>';
                    },
                },
            ],
        });
    }

    $(document).on('click', '.btn-delete-item', function () {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Hapus Proyek',
            text: 'Yakin ingin menghapus proyek ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: routes.destroyTemplate.replace('__ID__', id),
                method: 'DELETE',
                success: function () {
                    toastr.success('Proyek dihapus.');
                    $('#table-portfolio').DataTable().ajax.reload();
                },
                error: function () {
                    toastr.error('Gagal menghapus proyek.');
                },
            });
        });
    });

    function loadItemForEdit(id) {
        $('#cms-portfolio-form-title').text('Edit Proyek');
        $('#portfolio_id').val(id);

        $.getJSON(routes.showTemplate.replace('__ID__', id), function (item) {
            $('#title').val(item.title);
            $('#slug').val(item.slug);
            $('#excerpt').val(item.excerpt);
            $('#category').val(item.category);
            $('#location').val(item.location);
            $('#status').val(item.status);
            $('#show_on_glosspro').prop('checked', !!item.show_on_glosspro);
            $('#show_on_lexent').prop('checked', !!item.show_on_lexent);
            quill.root.innerHTML = item.body || '';

            var $gallery = $('#cms-existing-gallery').empty();
            (item.media || []).forEach(function (media) {
                $gallery.append(
                    '<div class="cms-gallery-item">' +
                        '<img src="' + media.thumbnail_url + '">' +
                        '<span class="remove-image remove-existing-image" data-id="' + media.id + '">&times;</span>' +
                    '</div>'
                );
            });
        });
    }

    function slugify(text) {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    }
});
```

- [ ] **Step 3: Add the sidebar link**

In `resources/views/layout/sidebar.blade.php`, the CMS section currently reads (from Phase 1/2):
```php
                <li class="nav-header">CMS</li>
                <li class="nav-item"><a href="/cms/articles" class="nav-link {{ Request::is('cms/articles*') ? 'active' : '' }}"><i class="nav-icon fas fa-newspaper"></i>
                        <p>Artikel</p>
                    </a></li>
                <li class="nav-item"><a href="/cms/catalog" class="nav-link {{ Request::is('cms/catalog*') ? 'active' : '' }}"><i class="nav-icon fas fa-boxes"></i>
                        <p>Katalog</p>
                    </a></li>
```
Add a third item right after it:
```php
                <li class="nav-item"><a href="/cms/portfolio" class="nav-link {{ Request::is('cms/portfolio*') ? 'active' : '' }}"><i class="nav-icon fas fa-images"></i>
                        <p>Portofolio</p>
                    </a></li>
```

- [ ] **Step 4: Manual smoke test — do this whole flow by hand in a browser**

1. `php artisan serve`, log in to the dashboard.
2. Go to CMS → Portofolio. The DataTable loads empty (0 records).
3. Click "Tambah Proyek". Confirm the slug auto-fills from the title, type body text in Quill, fill in "Lokasi" (e.g. `Jakarta`), check "GlossPro", pick 1 image, set status to "Published", submit.
4. Confirm redirect back to the list and the new row appears with a cover thumbnail and correct status/site.
5. Click Edit on that row — confirm all fields are pre-filled, **including the location field**, and the image gallery.
6. Delete the item from the list, confirm the SweetAlert2 prompt, confirm it's gone from the table.
7. Hit `GET /api/cms/portfolio?site=glosspro` with the `X-API-Key` header against a published item, confirm `location` comes back correctly in the detail response.

- [ ] **Step 5: Commit**

```bash
git add resources/views/cms/portfolio/index.blade.php public/js/cms_portfolio_function.js resources/views/layout/sidebar.blade.php
git commit -m "Add Portofolio admin list/form UI"
```

---

## Self-Review Notes

- **Spec coverage:** `cms_portfolio_items` schema incl. `location` ✅ (Task 1); model + morph map entry (now complete — all three content types registered) ✅ (Task 2); public API list+detail with the same shape as Artikel/Katalog ✅ (Task 3); admin CRUD + multi-image upload/delete ✅ (Task 4/5); sidebar entry ✅ (Task 5). The spec's `/portfolio` rewire in compro-1 and new `/portfolio` route in compro-2 are explicitly out of scope per the user's 2026-09-18 decision — see the Scope decision note; both become part of a later combined Artikel+Katalog+Portofolio consumer-integration plan per site.
- **Placeholder scan:** no TBD/TODO; every step has real, complete code.
- **Type consistency:** `PortfolioItem` field names, `ImageProcessor::process()` usage, and the API response keys (`slug`, `title`, `excerpt`, `category`, `published_at`, `cover`, `body`, `location`, `media`) match across Tasks 2, 3, 4, and 5, and mirror Phase 1/2's equivalents exactly except for the `location` vs `spec_highlights` field.
