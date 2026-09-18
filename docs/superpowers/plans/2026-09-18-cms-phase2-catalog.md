# CMS Phase 2 — Katalog (Sorotan Produk) (dashboard) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship the second CMS content type — Katalog ("Sorotan Produk") — end-to-end in the `dashboard` app: admin CRUD with multi-image upload and a repeatable `spec_highlights` key-value editor, plus a public JSON API that `compro-1`/`compro-2` will consume in a later, separate plan.

**Architecture:** New `cms_catalog_items` table, same shape as `cms_articles` (Phase 1) plus one extra `spec_highlights` JSON column. Reuses every piece of shared infrastructure Phase 1 already built and proved out: the polymorphic `cms_media` table, `Relation::morphMap()` (one new entry, `'catalog' => CatalogItem::class`), the `cms.api_key` middleware, the `ImageProcessor` service, and the admin layout's `@stack('styles')`/`@stack('scripts')` hooks. Admin CRUD lives at `/cms/catalog/*` behind the existing session-auth `web` middleware. Public API lives at `/api/cms/catalog/*` behind the existing `cms.api_key` middleware.

**Scope decision (confirmed with user 2026-09-18):** This plan is **dashboard-only** — same scoping choice as Phase 1. It does NOT touch `compro-1` or `compro-2`. Consumer-side integration (the `/sorotan` route + views in both sites, plus the still-outstanding `/artikel` consumer integration from Phase 1) is deferred to one later "CMS consumer integration" plan that builds a single `CmsClient` service per site and wires up Artikel and Katalog together, rather than writing and re-writing that plumbing once per content type.

**Tech Stack:** Laravel 8.83 / PHP 7.4.33 (this repo is PHP-7.4-pinned — see Global Constraints), `intervention/image` ^2.7 and `yajra/laravel-datatables-oracle` ^9.0 (already in use, no new Composer packages), Quill.js 1.3.7 via CDN (already wired into the admin layout by Phase 1).

**Spec:** `docs/superpowers/specs/2026-09-18-cms-design.md` (see "Fase 2 — Katalog (Sorotan Produk)" and the `cms_catalog_items` table definition)

**Prior phase:** `docs/superpowers/plans/2026-09-18-cms-phase1-articles.md` — read this first if the actual shape of `Article`, `CmsMedia`, `ImageProcessor`, or the admin/API controllers is unclear; this plan mirrors those exactly and calls out every place Katalog differs.

## Global Constraints

- **PHP 7.4 syntax only** — no constructor property promotion, no `match()`, no nullsafe `?->`, no native `enum`. Typed properties, arrow functions, and `??=` are fine.
- No `env()` calls outside `config/services.php` — this plan adds no new config keys (reuses `services.cms.api_key` from Phase 1).
- Do not touch the Warranty/Order/Invoice/Product(SKU) domain — no shared tables, no shared routes, no edits to those controllers/models.
- Do not touch `compro-1` or `compro-2` — see the Scope decision above.
- Follow the existing AdminLTE/jQuery/Toastr/SweetAlert2/DataTables admin conventions — **except** the form itself: Katalog's form (WYSIWYG body + multi-image upload + repeatable `spec_highlights` rows) doesn't fit a modal, so it gets real pages (`/cms/catalog/create`, `/cms/catalog/{catalogItem}/edit`), same deliberate deviation Phase 1 already established for Artikel.
- Auth: routes under `/cms/catalog` are protected automatically by the existing global `EnsureSessionAuthenticated` middleware — do not add a second auth check.
- Tests use the existing PHPUnit setup (`tests/Feature`, `RefreshDatabase`, `Login::factory()->create()` + `actingAs()`, `$this->withoutMiddleware(VerifyCsrfToken::class)` for POSTs). Run with `php artisan test`.
- `created_by` on `cms_catalog_items` is a plain nullable `integer`, same reasoning as `cms_articles.created_by` (type-matches `login.user_id`, no DB-level FK).
- No `SoftDeletes` — same reasoning as Phase 1: nothing in this plan builds a trash/restore UI, and it would create a slug-uniqueness footgun with no corresponding benefit.

---

### Task 1: Database schema — `cms_catalog_items`

**Files:**
- Create: `database/migrations/2026_09_18_000003_create_cms_catalog_items_table.php`
- Test: `tests/Feature/CmsCatalogItemSchemaTest.php`

**Interfaces:**
- Consumes: nothing new (the `cms_media` table from Phase 1 already supports any `mediable_type`).
- Produces: table `cms_catalog_items` — every column `cms_articles` has (id, title, slug unique, excerpt nullable, body longtext, category nullable, status default 'draft', published_at nullable, show_on_glosspro bool default false, show_on_lexent bool default false, created_by nullable int, timestamps) plus `spec_highlights` json nullable.

- [ ] **Step 1: Write the migration**

`database/migrations/2026_09_18_000003_create_cms_catalog_items_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_catalog_items', function (Blueprint $table) {
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
            $table->json('spec_highlights')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_catalog_items');
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

class CmsCatalogItemSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_cms_catalog_items_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('cms_catalog_items'));
        $this->assertTrue(Schema::hasColumns('cms_catalog_items', [
            'title', 'slug', 'excerpt', 'body', 'category', 'status',
            'published_at', 'show_on_glosspro', 'show_on_lexent', 'spec_highlights', 'created_by',
        ]));
    }
}
```

- [ ] **Step 3: Run the test to verify it passes**

Run: `php artisan test --filter=CmsCatalogItemSchemaTest`
Expected: PASS immediately (RefreshDatabase runs all migrations, including this new one, before the test). If it errors instead, the migration has a typo — fix and retry.

- [ ] **Step 4: Run the real migration against the local dev database**

Run: `php artisan migrate`
Expected: `Migrating: 2026_09_18_000003_create_cms_catalog_items_table` / `Migrated:`, no errors.

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_09_18_000003_create_cms_catalog_items_table.php tests/Feature/CmsCatalogItemSchemaTest.php
git commit -m "Add cms_catalog_items table"
```

---

### Task 2: Model — `CatalogItem`, morph map entry

**Files:**
- Create: `app/Models/CatalogItem.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Test: `tests/Unit/CatalogItemModelTest.php`

**Interfaces:**
- Consumes: `cms_catalog_items` table (Task 1), `CmsMedia` model (Phase 1).
- Produces: `CatalogItem::published()` scope, `CatalogItem::forSite(string $site)` scope, `CatalogItem->media()` relation, `CatalogItem->spec_highlights` as a plain PHP array (via `array` cast — Eloquent JSON-encodes/decodes automatically). Phase 3 (Portofolio) adds its own model + one more line in the same `morphMap()` call.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Unit;

use App\Models\CatalogItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogItemModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_relation_uses_the_short_morph_map_alias(): void
    {
        $item = CatalogItem::create([
            'title' => 'Produk Uji', 'slug' => 'produk-uji', 'body' => '<p>Isi</p>',
            'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => true,
        ]);

        $media = $item->media()->create([
            'path' => 'cms/catalog/1/a.webp', 'thumbnail_path' => 'cms/catalog/1/a-thumb.webp',
            'width' => 1920, 'height' => 1080, 'sort_order' => 0,
        ]);

        $this->assertSame('catalog', $media->fresh()->mediable_type);
        $this->assertTrue($item->media->first()->is($media));
    }

    public function test_published_scope_excludes_drafts_and_future_dated_items(): void
    {
        CatalogItem::create(['title' => 'Draft', 'slug' => 'draft', 'body' => 'x', 'status' => 'draft', 'show_on_glosspro' => true]);
        CatalogItem::create(['title' => 'Future', 'slug' => 'future', 'body' => 'x', 'status' => 'published', 'published_at' => now()->addDay(), 'show_on_glosspro' => true]);
        $live = CatalogItem::create(['title' => 'Live', 'slug' => 'live', 'body' => 'x', 'status' => 'published', 'published_at' => now()->subHour(), 'show_on_glosspro' => true]);

        $result = CatalogItem::published()->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($live));
    }

    public function test_for_site_scope_filters_by_the_matching_boolean_column(): void
    {
        $glosspro = CatalogItem::create(['title' => 'A', 'slug' => 'a', 'body' => 'x', 'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => true, 'show_on_lexent' => false]);
        CatalogItem::create(['title' => 'B', 'slug' => 'b', 'body' => 'x', 'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => false, 'show_on_lexent' => true]);

        $result = CatalogItem::published()->forSite('glosspro')->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($glosspro));
    }

    public function test_spec_highlights_round_trips_as_a_plain_array(): void
    {
        $item = CatalogItem::create([
            'title' => 'Dengan Spek', 'slug' => 'dengan-spek', 'body' => 'x', 'status' => 'draft',
            'spec_highlights' => [['label' => 'VLT', 'value' => '20%'], ['label' => 'Garansi', 'value' => '5 Thn']],
        ]);

        $fresh = $item->fresh();

        $this->assertIsArray($fresh->spec_highlights);
        $this->assertSame('VLT', $fresh->spec_highlights[0]['label']);
        $this->assertSame('5 Thn', $fresh->spec_highlights[1]['value']);
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --filter=CatalogItemModelTest`
Expected: FAIL — `Class "App\Models\CatalogItem" not found`.

- [ ] **Step 3: Write `app/Models/CatalogItem.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogItem extends Model
{
    use HasFactory;

    protected $table = 'cms_catalog_items';

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'category',
        'status', 'published_at', 'show_on_glosspro', 'show_on_lexent',
        'spec_highlights', 'created_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'show_on_glosspro' => 'boolean',
        'show_on_lexent' => 'boolean',
        'spec_highlights' => 'array',
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

The `boot()` method currently reads (from Phase 1):
```php
        Relation::morphMap([
            'article' => Article::class,
        ]);
```
Change it to:
```php
        Relation::morphMap([
            'article' => Article::class,
            'catalog' => CatalogItem::class,
        ]);
```
Add `use App\Models\CatalogItem;` alongside the existing `use App\Models\Article;` at the top of the file.

- [ ] **Step 5: Run the test to verify it passes**

Run: `php artisan test --filter=CatalogItemModelTest`
Expected: PASS (4 tests).

- [ ] **Step 6: Commit**

```bash
git add app/Models/CatalogItem.php app/Providers/AppServiceProvider.php tests/Unit/CatalogItemModelTest.php
git commit -m "Add CatalogItem model and register it in the CMS morph map"
```

---

### Task 3: Public API — `GET /api/cms/catalog` and `GET /api/cms/catalog/{slug}`

**Files:**
- Create: `app/Http/Controllers/Api/CatalogController.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/Api/CatalogApiTest.php`

**Interfaces:**
- Consumes: `CatalogItem::published()`, `CatalogItem::forSite()` (Task 2), `cms.api_key` middleware (Phase 1).
- Produces: list item shape `{slug, title, excerpt, category, published_at, cover}` — identical to Artikel's. Detail shape: list item shape + `{body, spec_highlights, media: [...]}`. `spec_highlights` is always an array (empty array, never `null`, when nothing was entered) so consumers never need a null-check.

- [ ] **Step 1: Write the failing tests**

```php
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
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --filter=CatalogApiTest`
Expected: FAIL — routes don't exist yet (404s from Laravel's default handler).

- [ ] **Step 3: Write `app/Http/Controllers/Api/CatalogController.php`**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CatalogItem;
use App\Models\CmsMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CatalogController extends Controller
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

        $items = CatalogItem::published()
            ->forSite($site)
            ->with('media')
            ->orderByDesc('published_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $items->getCollection()->map(function (CatalogItem $item) {
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

        $item = CatalogItem::published()->forSite($site)->with('media')->where('slug', $slug)->first();

        if (!$item) {
            return response()->json(['message' => 'Item katalog tidak ditemukan.'], 404);
        }

        $payload = $this->summarize($item);
        $payload['body'] = $item->body;
        $payload['spec_highlights'] = $item->spec_highlights ?: [];
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

    private function summarize(CatalogItem $item): array
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

In `routes/api.php`, add the import alongside the existing `use App\Http\Controllers\Api\ArticleController;` line, and add two routes inside the existing `cms.api_key` group:
```php
use App\Http\Controllers\Api\CatalogController;
```
```php
Route::middleware('cms.api_key')->prefix('cms')->group(function () {
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{slug}', [ArticleController::class, 'show']);
    Route::get('/catalog', [CatalogController::class, 'index']);
    Route::get('/catalog/{slug}', [CatalogController::class, 'show']);
});
```

- [ ] **Step 5: Run the tests to verify they pass**

Run: `php artisan test --filter=CatalogApiTest`
Expected: PASS (6 tests).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Api/CatalogController.php routes/api.php tests/Feature/Api/CatalogApiTest.php
git commit -m "Add public Katalog API (list + detail)"
```

---

### Task 4: Admin CRUD controller — create/edit/delete + image upload + `spec_highlights`

**Files:**
- Create: `app/Http/Controllers/Cms/CatalogController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Cms/CatalogAdminTest.php`

**Interfaces:**
- Consumes: `CatalogItem`, `CmsMedia` (Task 2), `ImageProcessor::process()` (Phase 1, unchanged).
- Produces: routes `cms.catalog.index|data|show|store|update|destroy|media.destroy`. `store`/`update` accept `images[]` (multipart file array, same as Artikel) plus `spec_highlights` as a bracket-indexed array of `{label, value}` pairs (e.g. form fields named `spec_highlights[0][label]`, `spec_highlights[0][value]`) — PHP parses this into `$request->input('spec_highlights')` as a plain array of associative arrays with no extra work. Rows where both `label` and `value` are blank are dropped before saving.

- [ ] **Step 1: Write the failing tests**

```php
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
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --filter=CatalogAdminTest`
Expected: FAIL — routes don't exist yet (404s).

- [ ] **Step 3: Write `app/Http/Controllers/Cms/CatalogController.php`**

```php
<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\CatalogItem;
use App\Models\CmsMedia;
use App\Services\ImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class CatalogController extends Controller
{
    private ImageProcessor $imageProcessor;

    public function __construct(ImageProcessor $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
    }

    public function index()
    {
        return view('cms.catalog.index', [
            'title' => 'Katalog',
            'navbar' => 'CMS - Katalog',
        ]);
    }

    public function data()
    {
        return DataTables::of(CatalogItem::query()->orderByDesc('id'))
            ->addColumn('cover', function (CatalogItem $item) {
                $cover = $item->media()->first();
                return $cover ? $cover->thumbnail_url : null;
            })
            ->addColumn('sites', function (CatalogItem $item) {
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

    public function show(CatalogItem $catalogItem)
    {
        $catalogItem->load('media');
        return response()->json($catalogItem);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = auth()->id();

        $item = CatalogItem::create($data);
        $this->storeUploadedImages($item, $request);

        return response()->json(['success' => true, 'id' => $item->id]);
    }

    public function update(Request $request, CatalogItem $catalogItem)
    {
        $data = $this->validated($request, $catalogItem->id);

        $catalogItem->update($data);
        $this->storeUploadedImages($catalogItem, $request);

        return response()->json(['success' => true]);
    }

    public function destroy(CatalogItem $catalogItem)
    {
        foreach ($catalogItem->media as $media) {
            $this->deleteMediaFiles($media);
        }

        $catalogItem->delete();

        return response()->json(['success' => true]);
    }

    public function destroyMedia(CmsMedia $media)
    {
        $this->deleteMediaFiles($media);

        return response()->json(['success' => true]);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = 'required|string|max:255|unique:cms_catalog_items,slug';
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
            'spec_highlights' => 'nullable|array',
            'spec_highlights.*.label' => 'nullable|string|max:100',
            'spec_highlights.*.value' => 'nullable|string|max:100',
        ]);

        $data['show_on_glosspro'] = $request->boolean('show_on_glosspro');
        $data['show_on_lexent'] = $request->boolean('show_on_lexent');
        $data['spec_highlights'] = $this->normalizedHighlights($request->input('spec_highlights', []));

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    private function normalizedHighlights(array $rows): array
    {
        return array_values(array_filter(array_map(function ($row) {
            $label = trim($row['label'] ?? '');
            $value = trim($row['value'] ?? '');

            return ($label === '' && $value === '') ? null : ['label' => $label, 'value' => $value];
        }, $rows)));
    }

    private function storeUploadedImages(CatalogItem $item, Request $request): void
    {
        if (!$request->hasFile('images')) {
            return;
        }

        $nextOrder = (int) $item->media()->max('sort_order') + 1;

        foreach ($request->file('images') as $file) {
            $processed = $this->imageProcessor->process($file, 'cms/catalog/' . $item->id);

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

In `routes/web.php`, add the import alongside the existing `use App\Http\Controllers\Cms\ArticleController;` line:
```php
use App\Http\Controllers\Cms\CatalogController;
```
And a new route group, right after the existing `cms/articles` group:
```php
// CMS - Katalog
Route::prefix('cms/catalog')->name('cms.catalog.')->group(function () {
    Route::get('/', [CatalogController::class, 'index'])->name('index');
    Route::get('/data', [CatalogController::class, 'data'])->name('data');
    Route::get('/create', [CatalogController::class, 'index'])->name('create');
    Route::get('/{catalogItem}/edit', [CatalogController::class, 'index'])->name('edit');
    Route::get('/{catalogItem}', [CatalogController::class, 'show'])->name('show');
    Route::post('/', [CatalogController::class, 'store'])->name('store');
    Route::put('/{catalogItem}', [CatalogController::class, 'update'])->name('update');
    Route::delete('/{catalogItem}', [CatalogController::class, 'destroy'])->name('destroy');
    Route::delete('/media/{media}', [CatalogController::class, 'destroyMedia'])->name('media.destroy');
});
```
Same ordering rule as Phase 1: `/data`, `/create`, `/{catalogItem}/edit` must be registered before the generic `/{catalogItem}` GET, or Laravel tries to route-model-bind the literal strings `"data"`/`"create"`.

- [ ] **Step 5: Run the tests to verify they pass**

Run: `php artisan test --filter=CatalogAdminTest`
Expected: PASS (5 tests).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Cms/CatalogController.php routes/web.php tests/Feature/Cms/CatalogAdminTest.php
git commit -m "Add Katalog admin CRUD with spec_highlights and multi-image upload"
```

---

### Task 5: Admin views — Katalog list + create/edit form + sidebar link

**Files:**
- Create: `resources/views/cms/catalog/index.blade.php`
- Create: `public/js/cms_catalog_function.js`
- Modify: `resources/views/layout/sidebar.blade.php`

**Interfaces:**
- Consumes: routes from Task 4 (`cms.catalog.data`, `.store`, `.update`, `.show`, `.destroy`, `.media.destroy`), `@stack` hooks (already in the layout since Phase 1).
- Produces: a working browser UI. No automated test (same reasoning as Phase 1 Task 8 — this codebase has no Blade/browser test tooling); verification is manual, listed as the final step.

- [ ] **Step 1: Write `resources/views/cms/catalog/index.blade.php`**

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
    .cms-highlight-row { display: flex; gap: 10px; margin-bottom: 8px; align-items: center; }
    .cms-highlight-row input { flex: 1; }
    .cms-highlight-row .remove-highlight-row { cursor: pointer; color: #dc3545; }
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6"><h1>Katalog</h1></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        {{-- List view --}}
        <div id="cms-catalog-list-panel" class="card">
            <div class="card-header">
                <a href="{{ route('cms.catalog.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Item Katalog</a>
            </div>
            <div class="card-body">
                <table id="table-catalog" class="table table-bordered table-striped" style="width:100%">
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
        <div id="cms-catalog-form-panel" class="card" style="display:none;">
            <div class="card-header">
                <h3 class="card-title" id="cms-catalog-form-title">Tambah Item Katalog</h3>
            </div>
            <form id="form-catalog">
                @csrf
                <input type="hidden" id="catalog_id" name="catalog_id">
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
                        <label>Spesifikasi Unggulan (opsional)</label>
                        <div id="cms-highlight-rows"></div>
                        <button type="button" id="btn-add-highlight" class="btn btn-sm btn-secondary"><i class="fas fa-plus"></i> Tambah Spek</button>
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
                    <a href="{{ route('cms.catalog.index') }}" class="btn btn-default">Batal</a>
                </div>
            </form>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
    window.CMS_CATALOG_ROUTES = {
        data: "{{ route('cms.catalog.data') }}",
        store: "{{ route('cms.catalog.store') }}",
        index: "{{ route('cms.catalog.index') }}",
        showTemplate: "{{ route('cms.catalog.show', ['catalogItem' => '__ID__']) }}",
        updateTemplate: "{{ route('cms.catalog.update', ['catalogItem' => '__ID__']) }}",
        destroyTemplate: "{{ route('cms.catalog.destroy', ['catalogItem' => '__ID__']) }}",
        mediaDestroyTemplate: "{{ route('cms.catalog.media.destroy', ['media' => '__ID__']) }}",
        editTemplate: "{{ route('cms.catalog.edit', ['catalogItem' => '__ID__']) }}",
        createUrl: "{{ route('cms.catalog.create') }}",
    };
    window.CMS_CATALOG_EDIT_ID = @json(request()->routeIs('cms.catalog.edit') ? request()->route('catalogItem') : null);
    window.CMS_CATALOG_IS_CREATE = @json(request()->routeIs('cms.catalog.create'));
</script>
<script src="js/cms_catalog_function.js?v={{ filemtime(public_path('js/cms_catalog_function.js')) }}"></script>
@endpush
```

- [ ] **Step 2: Write `public/js/cms_catalog_function.js`**

```javascript
$(function () {
    var routes = window.CMS_CATALOG_ROUTES;
    var isFormMode = window.CMS_CATALOG_IS_CREATE || !!window.CMS_CATALOG_EDIT_ID;
    var quill = null;
    var highlightIndex = 0;

    if (isFormMode) {
        $('#cms-catalog-list-panel').hide();
        $('#cms-catalog-form-panel').show();
        quill = new Quill('#quill-body-editor', { theme: 'snow' });
        addHighlightRow();
    } else {
        initDataTable();
    }

    if (window.CMS_CATALOG_EDIT_ID) {
        loadItemForEdit(window.CMS_CATALOG_EDIT_ID);
    }

    $('#title').on('input', function () {
        if (!window.CMS_CATALOG_EDIT_ID) {
            $('#slug').val(slugify($(this).val()));
        }
    });

    $('#btn-add-highlight').on('click', function () {
        addHighlightRow();
    });

    $(document).on('click', '.remove-highlight-row', function () {
        $(this).closest('.cms-highlight-row').remove();
    });

    $('#form-catalog').on('submit', function (e) {
        e.preventDefault();
        $('#body').val(quill.root.innerHTML);

        var formData = new FormData(this);
        var id = $('#catalog_id').val();
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
                toastr.success('Item katalog berhasil disimpan.');
                window.location.href = routes.index;
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors || {};
                    var firstMessage = Object.values(errors)[0];
                    toastr.error(firstMessage ? firstMessage[0] : 'Data tidak valid.');
                } else {
                    toastr.error('Gagal menyimpan item katalog.');
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

    function addHighlightRow(label, value) {
        var index = highlightIndex++;
        var $row = $(
            '<div class="cms-highlight-row">' +
                '<input type="text" class="form-control" name="spec_highlights[' + index + '][label]" placeholder="Label (mis. VLT)" value="' + (label || '') + '">' +
                '<input type="text" class="form-control" name="spec_highlights[' + index + '][value]" placeholder="Nilai (mis. 20%)" value="' + (value || '') + '">' +
                '<i class="fas fa-times remove-highlight-row"></i>' +
            '</div>'
        );
        $('#cms-highlight-rows').append($row);
    }

    function initDataTable() {
        $('#table-catalog').DataTable({
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
            title: 'Hapus Item Katalog',
            text: 'Yakin ingin menghapus item ini?',
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
                    toastr.success('Item katalog dihapus.');
                    $('#table-catalog').DataTable().ajax.reload();
                },
                error: function () {
                    toastr.error('Gagal menghapus item katalog.');
                },
            });
        });
    });

    function loadItemForEdit(id) {
        $('#cms-catalog-form-title').text('Edit Item Katalog');
        $('#catalog_id').val(id);

        $.getJSON(routes.showTemplate.replace('__ID__', id), function (item) {
            $('#title').val(item.title);
            $('#slug').val(item.slug);
            $('#excerpt').val(item.excerpt);
            $('#category').val(item.category);
            $('#status').val(item.status);
            $('#show_on_glosspro').prop('checked', !!item.show_on_glosspro);
            $('#show_on_lexent').prop('checked', !!item.show_on_lexent);
            quill.root.innerHTML = item.body || '';

            $('#cms-highlight-rows').empty();
            var highlights = item.spec_highlights || [];
            if (highlights.length === 0) {
                addHighlightRow();
            } else {
                highlights.forEach(function (row) {
                    addHighlightRow(row.label, row.value);
                });
            }

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

In `resources/views/layout/sidebar.blade.php`, the CMS section currently reads (from Phase 1):
```php
                <li class="nav-header">CMS</li>
                <li class="nav-item"><a href="/cms/articles" class="nav-link {{ Request::is('cms/articles*') ? 'active' : '' }}"><i class="nav-icon fas fa-newspaper"></i>
                        <p>Artikel</p>
                    </a></li>
```
Add a second item right after it, still inside the same `CMS` group:
```php
                <li class="nav-item"><a href="/cms/catalog" class="nav-link {{ Request::is('cms/catalog*') ? 'active' : '' }}"><i class="nav-icon fas fa-boxes"></i>
                        <p>Katalog</p>
                    </a></li>
```

- [ ] **Step 4: Manual smoke test — do this whole flow by hand in a browser**

1. `php artisan serve`, log in to the dashboard.
2. Go to CMS → Katalog. The DataTable loads empty (0 records).
3. Click "Tambah Item Katalog". Confirm the slug auto-fills from the title, type body text in Quill, click "Tambah Spek" twice and fill in two label/value pairs (e.g. `VLT` / `20%` and `Garansi` / `5 Thn`), check "GlossPro", pick 1 image, set status to "Published", submit.
4. Confirm redirect back to the list and the new row appears with a cover thumbnail and correct status/site.
5. Click Edit on that row — confirm all fields are pre-filled, **including both spec-highlight rows with their original label/value**, and the image gallery.
6. Add a third spec-highlight row, remove the first one via its × icon, save, re-open edit, confirm exactly 2 rows remain with the expected values.
7. Delete the item from the list, confirm the SweetAlert2 prompt, confirm it's gone from the table.
8. Hit `GET /api/cms/catalog?site=glosspro` with the `X-API-Key` header against a published item, confirm `spec_highlights` comes back as an array in the detail response.

- [ ] **Step 5: Commit**

```bash
git add resources/views/cms/catalog/index.blade.php public/js/cms_catalog_function.js resources/views/layout/sidebar.blade.php
git commit -m "Add Katalog admin list/form UI with spec_highlights editor"
```

---

## Self-Review Notes

- **Spec coverage:** `cms_catalog_items` schema incl. `spec_highlights` ✅ (Task 1); model + morph map entry ✅ (Task 2); public API list+detail with the same pagination/excerpt-fallback shape as Artikel, plus `spec_highlights` always an array ✅ (Task 3); admin CRUD + `spec_highlights` repeatable editor + multi-image upload/delete ✅ (Task 4/5); sidebar entry ✅ (Task 5). Consumer-side `/sorotan` integration in compro-1/compro-2 is explicitly out of scope per the user's 2026-09-18 decision — see the Scope decision note; it becomes part of a later combined Artikel+Katalog consumer-integration plan.
- **Placeholder scan:** no TBD/TODO; every step has real, complete code.
- **Type consistency:** `CatalogItem` field names, the `spec_highlights` array shape (`[{label, value}]`), `ImageProcessor::process()` usage, and the API response keys (`slug`, `title`, `excerpt`, `category`, `published_at`, `cover`, `body`, `spec_highlights`, `media`) match across Tasks 2, 3, 4, and 5 — checked against each other and against Phase 1's equivalents while writing.
