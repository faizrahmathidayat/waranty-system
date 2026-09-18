# CMS Phase 1 — Infrastruktur + Artikel (dashboard) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the shared CMS infrastructure (media storage, image resize/WebP pipeline, API-key auth) in the `dashboard` app, and ship the first content type — Artikel — end-to-end: admin CRUD with multi-image upload, and a public JSON API that `compro-1`/`compro-2` will consume in the next plans.

**Architecture:** New `cms_articles` + `cms_media` tables (polymorphic media, shared by Katalog/Portofolio in later phases via `Relation::morphMap`). Admin CRUD lives at `/cms/articles/*` behind the existing session-auth `web` middleware (already global). Public API lives at `/api/cms/articles/*` behind a new `X-API-Key` middleware, key sourced from `config('services.cms.api_key')`. Images are processed through a new `ImageProcessor` service (`intervention/image`, already installed) into a full (≤1920px) and thumbnail (≤480px) WebP pair per upload, never upscaled.

**Tech Stack:** Laravel 8.83 / PHP 7.4.33 (this repo is PHP-7.4-pinned — see Global Constraints), `intervention/image` ^2.7, `yajra/laravel-datatables-oracle` ^9.0 (both already in `composer.json`, no new Composer packages needed for this plan), Quill.js 1.3.7 via CDN (admin body editor, not loaded on public-facing anything since this repo has no public-facing pages of its own).

**Spec:** `docs/superpowers/specs/2026-09-18-cms-design.md`

## Global Constraints

- **PHP 7.4 syntax only** — no constructor property promotion, no `match()`, no nullsafe `?->`, no native `enum`. Use classic constructors, `switch`/`if`, the `optional()` helper, and string constants instead. (Typed properties, arrow functions, and `??=` are fine — those are 7.4-legal.)
- No `env()` calls outside `config/services.php`. The API key is `config('services.cms.api_key')` everywhere else.
- Do not touch the Warranty/Order/Invoice/Product(SKU) domain — no shared tables, no shared routes, no edits to those controllers/models.
- Follow the existing AdminLTE/jQuery/Toastr/SweetAlert2/DataTables admin conventions (`resources/views/layout/layout.blade.php`, per-module `public/js/*_function.js` files) — **except** the form itself: existing CRUD here uses small Bootstrap modals, but Artikel's form (WYSIWYG body + multi-image upload/reorder) doesn't fit a modal, so it gets real pages (`/cms/articles/create`, `/cms/articles/{article}/edit`) instead. This is a deliberate, scoped deviation — don't modal-ize it back, and don't apply this pattern to unrelated existing modules.
- Auth: routes under `/cms/articles` are protected automatically by the existing global `EnsureSessionAuthenticated` middleware (already in the `web` group in `app/Http/Kernel.php`) — do not add a second auth check.
- Tests use the existing PHPUnit setup (`tests/Feature`, `RefreshDatabase`, `Login::factory()->create()` + `actingAs()`, `$this->withoutMiddleware(VerifyCsrfToken::class)` for POSTs) — see `tests/Feature/CustomerVehicleBuildingTest.php` for the exact pattern to match. Run with `php artisan test`.
- `created_by` on `cms_articles` is a plain nullable `integer` (not `unsignedBigInteger`) because `login.user_id` (the column it conceptually points to) is `integer` — no DB-level foreign key constraint, just type-matched.

---

### Task 1: Database schema — `cms_articles` and `cms_media`

**Files:**
- Create: `database/migrations/2026_09_18_000001_create_cms_articles_table.php`
- Create: `database/migrations/2026_09_18_000002_create_cms_media_table.php`
- Test: `tests/Feature/CmsArticleSchemaTest.php`

**Interfaces:**
- Produces: table `cms_articles` (id, title, slug unique, excerpt nullable, body longtext, category nullable, status default 'draft', published_at nullable, show_on_glosspro bool default false, show_on_lexent bool default false, created_by nullable int, timestamps — hard delete, no soft-delete: nothing in this plan builds a trash/restore UI, so a `deleted_at` column would only create a slug-uniqueness footgun with no corresponding benefit). Table `cms_media` (id, mediable_type, mediable_id, path, thumbnail_path, width, height, sort_order default 0, alt_text nullable, timestamps, index on [mediable_type, mediable_id]).

- [ ] **Step 1: Write the migrations**

`database/migrations/2026_09_18_000001_create_cms_articles_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_articles', function (Blueprint $table) {
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
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_articles');
    }
};
```

`database/migrations/2026_09_18_000002_create_cms_media_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_media', function (Blueprint $table) {
            $table->id();
            $table->string('mediable_type');
            $table->unsignedBigInteger('mediable_id');
            $table->string('path');
            $table->string('thumbnail_path');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('alt_text')->nullable();
            $table->timestamps();

            $table->index(['mediable_type', 'mediable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_media');
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

class CmsArticleSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_cms_articles_and_cms_media_tables_exist_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('cms_articles'));
        $this->assertTrue(Schema::hasColumns('cms_articles', [
            'title', 'slug', 'excerpt', 'body', 'category', 'status',
            'published_at', 'show_on_glosspro', 'show_on_lexent', 'created_by',
        ]));

        $this->assertTrue(Schema::hasTable('cms_media'));
        $this->assertTrue(Schema::hasColumns('cms_media', [
            'mediable_type', 'mediable_id', 'path', 'thumbnail_path', 'width', 'height', 'sort_order', 'alt_text',
        ]));
    }
}
```

- [ ] **Step 3: Run the test to verify it fails (tables don't exist yet is expected to already pass once migrated by RefreshDatabase — instead verify the migrations themselves apply cleanly)**

Run: `php artisan migrate --env=testing` then `php artisan test --filter=CmsArticleSchemaTest`
Expected: if the migration files have a typo, `migrate` errors out here — fix and retry until it applies cleanly, then the test should PASS immediately (RefreshDatabase runs migrations before each test).

- [ ] **Step 4: Run the real migration against the local dev database**

Run: `php artisan migrate`
Expected: `Migrating: 2026_09_18_000001_create_cms_articles_table` / `Migrated:` then the same for `..._000002_create_cms_media_table`, no errors.

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_09_18_000001_create_cms_articles_table.php database/migrations/2026_09_18_000002_create_cms_media_table.php tests/Feature/CmsArticleSchemaTest.php
git commit -m "Add cms_articles and cms_media tables"
```

---

### Task 2: Models — `Article`, `CmsMedia`, morph map

**Files:**
- Create: `app/Models/Article.php`
- Create: `app/Models/CmsMedia.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Test: `tests/Unit/ArticleModelTest.php`

**Interfaces:**
- Consumes: `cms_articles`/`cms_media` tables from Task 1.
- Produces: `Article::published()` scope, `Article::forSite(string $site)` scope, `Article->media()` relation (ordered by `sort_order`), `CmsMedia->url` / `CmsMedia->thumbnail_url` accessors, `CmsMedia->mediable()` morphTo. Later phases (Katalog/Portofolio) add their own model + one more line in the same `morphMap()` call.

- [ ] **Step 1: Write the failing test**

```php
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
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --filter=ArticleModelTest`
Expected: FAIL — `Class "App\Models\Article" not found`.

- [ ] **Step 3: Write `app/Models/Article.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $table = 'cms_articles';

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'category',
        'status', 'published_at', 'show_on_glosspro', 'show_on_lexent', 'created_by',
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

- [ ] **Step 4: Write `app/Models/CmsMedia.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsMedia extends Model
{
    protected $table = 'cms_media';

    protected $fillable = [
        'mediable_type', 'mediable_id', 'path', 'thumbnail_path',
        'width', 'height', 'sort_order', 'alt_text',
    ];

    protected $appends = ['url', 'thumbnail_url'];

    public function mediable()
    {
        return $this->morphTo();
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        return asset('storage/' . $this->thumbnail_path);
    }
}
```

- [ ] **Step 5: Register the morph map in `app/Providers/AppServiceProvider.php`**

Replace the whole file with:
```php
<?php

namespace App\Providers;

use App\Models\Article;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Short aliases for cms_media.mediable_type, instead of full class
        // names, so the DB column stays readable. Katalog/Portofolio add
        // their own entry here in Phase 2/3 — same array, one line each.
        Relation::morphMap([
            'article' => Article::class,
        ]);
    }
}
```

- [ ] **Step 6: Run the test to verify it passes**

Run: `php artisan test --filter=ArticleModelTest`
Expected: PASS (3 tests).

- [ ] **Step 7: Commit**

```bash
git add app/Models/Article.php app/Models/CmsMedia.php app/Providers/AppServiceProvider.php tests/Unit/ArticleModelTest.php
git commit -m "Add Article/CmsMedia models with morph map"
```

---

### Task 3: API-key config and middleware

**Files:**
- Modify: `.env` (dev machine only, not committed)
- Modify: `.env.testing`
- Modify: `config/services.php`
- Create: `app/Http/Middleware/VerifyCmsApiKey.php`
- Modify: `app/Http/Kernel.php`
- Test: `tests/Feature/VerifyCmsApiKeyTest.php`

**Interfaces:**
- Produces: route middleware alias `'cms.api_key'`, `config('services.cms.api_key')`.

- [ ] **Step 1: Add the config entry**

Edit `config/services.php`, add before the closing `];`:
```php
    'cms' => [
        'api_key' => env('CMS_API_KEY'),
    ],
```

- [ ] **Step 2: Add `CMS_API_KEY` to `.env` and `.env.testing`**

Generate a real secret for local dev:
```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```
Append to `.env`: `CMS_API_KEY=<paste the generated value>`
Append to `.env.testing`: `CMS_API_KEY=test-cms-api-key-do-not-use-in-prod`

- [ ] **Step 3: Write the failing test**

```php
<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class VerifyCmsApiKeyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('cms.api_key')->get('/__test/cms-api-key-probe', function () {
            return response()->json(['ok' => true]);
        });
    }

    public function test_request_without_key_is_rejected(): void
    {
        $this->getJson('/__test/cms-api-key-probe')->assertStatus(401);
    }

    public function test_request_with_wrong_key_is_rejected(): void
    {
        $this->getJson('/__test/cms-api-key-probe', ['X-API-Key' => 'wrong'])
            ->assertStatus(401);
    }

    public function test_request_with_correct_key_is_accepted(): void
    {
        $this->getJson('/__test/cms-api-key-probe', ['X-API-Key' => config('services.cms.api_key')])
            ->assertStatus(200)
            ->assertJson(['ok' => true]);
    }
}
```

- [ ] **Step 4: Run the test to verify it fails**

Run: `php artisan test --filter=VerifyCmsApiKeyTest`
Expected: FAIL — `'cms.api_key'` middleware alias not defined (`Target class [cms.api_key] does not exist`), since neither the middleware class nor the alias exist yet.

- [ ] **Step 5: Write `app/Http/Middleware/VerifyCmsApiKey.php`**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyCmsApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $expected = config('services.cms.api_key');
        $provided = $request->header('X-API-Key');

        if (empty($expected) || empty($provided) || !hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Invalid or missing API key.'], 401);
        }

        return $next($request);
    }
}
```

- [ ] **Step 6: Register the alias in `app/Http/Kernel.php`**

Add one line inside `$routeMiddleware` (after `'cache.headers'` is a fine spot, alphabetical-ish):
```php
        'cms.api_key' => \App\Http\Middleware\VerifyCmsApiKey::class,
```

- [ ] **Step 7: Run the test to verify it passes**

Run: `php artisan test --filter=VerifyCmsApiKeyTest`
Expected: PASS (3 tests).

- [ ] **Step 8: Commit**

```bash
git add config/services.php app/Http/Middleware/VerifyCmsApiKey.php app/Http/Kernel.php .env.testing tests/Feature/VerifyCmsApiKeyTest.php
git commit -m "Add CMS API key config and verification middleware"
```

(`.env` itself is gitignored — nothing to add there.)

---

### Task 4: Image processing service (resize + WebP)

**Files:**
- Create: `app/Services/ImageProcessor.php`
- Test: `tests/Unit/ImageProcessorTest.php`

**Interfaces:**
- Consumes: `Illuminate\Http\UploadedFile`.
- Produces: `ImageProcessor::process(UploadedFile $file, string $directory): array` returning `['path' => string, 'thumbnail_path' => string, 'width' => int, 'height' => int]`, both files written under `storage/app/public/{$directory}/`.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Unit;

use App\Services\ImageProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageProcessorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_large_image_is_resized_down_and_converted_to_webp(): void
    {
        $file = UploadedFile::fake()->image('big.jpg', 3000, 2000);

        $result = (new ImageProcessor())->process($file, 'cms/articles/999');

        $this->assertSame('.webp', substr($result['path'], -5));
        $this->assertSame('.webp', substr($result['thumbnail_path'], -5));
        $this->assertLessThanOrEqual(1920, $result['width']);
        Storage::disk('public')->assertExists($result['path']);
        Storage::disk('public')->assertExists($result['thumbnail_path']);
    }

    public function test_small_image_is_never_upscaled(): void
    {
        $file = UploadedFile::fake()->image('small.jpg', 300, 200);

        $result = (new ImageProcessor())->process($file, 'cms/articles/999');

        $this->assertSame(300, $result['width']);
        $this->assertSame(200, $result['height']);
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --filter=ImageProcessorTest`
Expected: FAIL — `Class "App\Services\ImageProcessor" not found`.

- [ ] **Step 3: Write `app/Services/ImageProcessor.php`**

```php
<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageProcessor
{
    private const FULL_MAX_WIDTH = 1920;
    private const THUMB_MAX_WIDTH = 480;
    private const QUALITY_FULL = 80;
    private const QUALITY_THUMB = 75;

    /**
     * @return array{path: string, thumbnail_path: string, width: int, height: int}
     */
    public function process(UploadedFile $file, string $directory): array
    {
        $directory = trim($directory, '/');
        $uuid = (string) Str::uuid();

        $full = Image::make($file->getRealPath());
        if ($full->width() > self::FULL_MAX_WIDTH) {
            $full->resize(self::FULL_MAX_WIDTH, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        $fullRelative = $directory . '/' . $uuid . '.webp';
        $this->save($full, $fullRelative, self::QUALITY_FULL);

        $thumb = Image::make($file->getRealPath());
        $thumb->resize(self::THUMB_MAX_WIDTH, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $thumbRelative = $directory . '/' . $uuid . '-thumb.webp';
        $this->save($thumb, $thumbRelative, self::QUALITY_THUMB);

        return [
            'path' => $fullRelative,
            'thumbnail_path' => $thumbRelative,
            'width' => $full->width(),
            'height' => $full->height(),
        ];
    }

    private function save($image, string $relativePath, int $quality): void
    {
        $absolute = storage_path('app/public/' . $relativePath);
        $dir = dirname($absolute);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $image->encode('webp', $quality)->save($absolute);
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `php artisan test --filter=ImageProcessorTest`
Expected: PASS (2 tests). If it fails with a GD/webp-support error, check `php -m | grep -i gd` and that the GD build has WebP support (`php -i | grep -i webp`) — this PHP 7.4 install already has GD (used elsewhere in this repo for QR codes via `simplesoftwareio/simple-qrcode`), so this should work out of the box, but confirm before moving on.

- [ ] **Step 5: Commit**

```bash
git add app/Services/ImageProcessor.php tests/Unit/ImageProcessorTest.php
git commit -m "Add ImageProcessor service (resize + WebP conversion)"
```

---

### Task 5: Public API — `GET /api/cms/articles` and `GET /api/cms/articles/{slug}`

**Files:**
- Create: `app/Http/Controllers/Api/ArticleController.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/Api/ArticleApiTest.php`

**Interfaces:**
- Consumes: `Article::published()`, `Article::forSite()` (Task 2), `cms.api_key` middleware (Task 3).
- Produces: the two routes below. List item shape: `{slug, title, excerpt, category, published_at, cover}` where `cover` is `{url, thumbnail_url, width, height, alt_text}` or `null`. Detail shape: list item shape + `{body, media: [cover-shape, ...]}`. compro-1/compro-2's Phase-2 plans consume these exact keys.

- [ ] **Step 1: Write the failing tests**

```php
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
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --filter=ArticleApiTest`
Expected: FAIL — 404s from Laravel's default "route not found" rather than the app's own 404/401/400 JSON, since neither the controller nor the routes exist yet.

- [ ] **Step 3: Write `app/Http/Controllers/Api/ArticleController.php`**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\CmsMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
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

        $articles = Article::published()
            ->forSite($site)
            ->with('media')
            ->orderByDesc('published_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $articles->getCollection()->map(function (Article $article) {
                return $this->summarize($article);
            })->all(),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $site = $this->validateSite($request);
        if ($site === null) {
            return response()->json(['message' => 'Parameter site wajib diisi dengan glosspro atau lexent.'], 400);
        }

        $article = Article::published()->forSite($site)->with('media')->where('slug', $slug)->first();

        if (!$article) {
            return response()->json(['message' => 'Artikel tidak ditemukan.'], 404);
        }

        $payload = $this->summarize($article);
        $payload['body'] = $article->body;
        $payload['media'] = $article->media->map(function (CmsMedia $media) {
            return $this->mediaPayload($media);
        })->all();

        return response()->json(['data' => $payload]);
    }

    private function validateSite(Request $request): ?string
    {
        $site = $request->query('site');
        return in_array($site, self::ALLOWED_SITES, true) ? $site : null;
    }

    private function summarize(Article $article): array
    {
        $cover = $article->media->first();
        $excerpt = $article->excerpt ?: Str::limit(trim(strip_tags($article->body)), 160);

        return [
            'slug' => $article->slug,
            'title' => $article->title,
            'excerpt' => $excerpt,
            'category' => $article->category,
            'published_at' => optional($article->published_at)->toIso8601String(),
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

Append to `routes/api.php` (below the existing `/user` route):
```php
use App\Http\Controllers\Api\ArticleController;

Route::middleware('cms.api_key')->prefix('cms')->group(function () {
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{slug}', [ArticleController::class, 'show']);
});
```
(Add the `use` import at the top of the file alongside the existing `use Illuminate\Http\Request;` line.)

- [ ] **Step 5: Run the tests to verify they pass**

Run: `php artisan test --filter=ArticleApiTest`
Expected: PASS (6 tests).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Api/ArticleController.php routes/api.php tests/Feature/Api/ArticleApiTest.php
git commit -m "Add public Artikel API (list + detail)"
```

---

### Task 6: Admin CRUD controller — create/edit/delete + image upload

**Files:**
- Create: `app/Http/Controllers/Cms/ArticleController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Cms/ArticleAdminTest.php`

**Interfaces:**
- Consumes: `Article`, `CmsMedia` (Task 2), `ImageProcessor::process()` (Task 4).
- Produces: routes `cms.articles.index|data|show|store|update|destroy|media.destroy`, all returning JSON (`{success: true, ...}` or the record), matching this app's AJAX-form convention. `store`/`update` accept `images[]` (multipart file array) in addition to the text fields.

- [ ] **Step 1: Write the failing tests**

```php
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
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --filter=ArticleAdminTest`
Expected: FAIL — routes don't exist yet (404s).

- [ ] **Step 3: Write `app/Http/Controllers/Cms/ArticleController.php`**

```php
<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\CmsMedia;
use App\Services\ImageProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ArticleController extends Controller
{
    private ImageProcessor $imageProcessor;

    public function __construct(ImageProcessor $imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
    }

    public function index()
    {
        return view('cms.articles.index', [
            'title' => 'Artikel',
            'navbar' => 'CMS - Artikel',
        ]);
    }

    public function data()
    {
        return DataTables::of(Article::query()->orderByDesc('id'))
            ->addColumn('cover', function (Article $article) {
                $cover = $article->media()->first();
                return $cover ? $cover->thumbnail_url : null;
            })
            ->addColumn('sites', function (Article $article) {
                $sites = [];
                if ($article->show_on_glosspro) {
                    $sites[] = 'GlossPro';
                }
                if ($article->show_on_lexent) {
                    $sites[] = 'LEXENT';
                }
                return implode(', ', $sites) ?: '-';
            })
            ->toJson();
    }

    public function show(Article $article)
    {
        $article->load('media');
        return response()->json($article);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = auth()->id();

        $article = Article::create($data);
        $this->storeUploadedImages($article, $request);

        return response()->json(['success' => true, 'id' => $article->id]);
    }

    public function update(Request $request, Article $article)
    {
        $data = $this->validated($request, $article->id);

        $article->update($data);
        $this->storeUploadedImages($article, $request);

        return response()->json(['success' => true]);
    }

    public function destroy(Article $article)
    {
        foreach ($article->media as $media) {
            $this->deleteMediaFiles($media);
        }

        $article->delete();

        return response()->json(['success' => true]);
    }

    public function destroyMedia(CmsMedia $media)
    {
        $this->deleteMediaFiles($media);

        return response()->json(['success' => true]);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = 'required|string|max:255|unique:cms_articles,slug';
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
        ]);

        $data['show_on_glosspro'] = $request->boolean('show_on_glosspro');
        $data['show_on_lexent'] = $request->boolean('show_on_lexent');

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    private function storeUploadedImages(Article $article, Request $request): void
    {
        if (!$request->hasFile('images')) {
            return;
        }

        $nextOrder = (int) $article->media()->max('sort_order') + 1;

        foreach ($request->file('images') as $file) {
            $processed = $this->imageProcessor->process($file, 'cms/articles/' . $article->id);

            $article->media()->create(array_merge($processed, [
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

Append to `routes/web.php` (add the `use` import near the other controller imports, and the route group near the bottom, before the closing of the file):
```php
use App\Http\Controllers\Cms\ArticleController;

// CMS - Artikel
Route::prefix('cms/articles')->name('cms.articles.')->group(function () {
    Route::get('/', [ArticleController::class, 'index'])->name('index');
    Route::get('/data', [ArticleController::class, 'data'])->name('data');
    Route::get('/create', [ArticleController::class, 'index'])->name('create');
    Route::get('/{article}/edit', [ArticleController::class, 'index'])->name('edit');
    Route::get('/{article}', [ArticleController::class, 'show'])->name('show');
    Route::post('/', [ArticleController::class, 'store'])->name('store');
    Route::put('/{article}', [ArticleController::class, 'update'])->name('update');
    Route::delete('/{article}', [ArticleController::class, 'destroy'])->name('destroy');
    Route::delete('/media/{media}', [ArticleController::class, 'destroyMedia'])->name('media.destroy');
});
```

Note the order: `/data`, `/create` and `/{article}/edit` are registered **before** the generic `/{article}` GET route, otherwise Laravel would try to route-model-bind `article` with the literal string `"data"` or `"create"` and 404. `index()` is reused for `/create` and `/{article}/edit` too — Task 7's view reads the current URL client-side to decide create-vs-edit mode (see Task 8), so one controller method can serve all three page loads.

- [ ] **Step 5: Run the tests to verify they pass**

Run: `php artisan test --filter=ArticleAdminTest`
Expected: PASS (6 tests).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Cms/ArticleController.php routes/web.php tests/Feature/Cms/ArticleAdminTest.php
git commit -m "Add Artikel admin CRUD with multi-image upload"
```

---

### Task 7: Admin layout hooks + sidebar link

**Files:**
- Modify: `resources/views/layout/layout.blade.php`
- Modify: `resources/views/layout/sidebar.blade.php`

**Interfaces:**
- Produces: `@stack('styles')` / `@stack('scripts')` hooks any future admin page can push into; a "CMS" sidebar section linking to `/cms/articles`.

- [ ] **Step 1: Add stack hooks to the layout**

In `resources/views/layout/layout.blade.php`, add one line right before `</head>`... this file has no explicit `</head>` tag written (the `<head>` block ends implicitly where `<!-- Theme style -->`'s `<link>` is, right before the two commented-out lines and closing). Add directly after the `<!-- Theme style -->` block (after the `dist/css/adminlte.min.css` link, before the commented-out `tabel.css` line):
```php
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css">

    @stack('styles')

```
And near the very end of the file, immediately before the closing `</body>`, add:
```php
    @stack('scripts')

</body>
```

- [ ] **Step 2: Add the sidebar section**

In `resources/views/layout/sidebar.blade.php`, insert a new `nav-header` + link right after the `MASTER DATA` block's last item (`/user`, right before the `REPORTS` `nav-header` at line 110 in the current file) — i.e. right after the `</li>` that closes the "Data User" `<li>`:
```php
                <li class="nav-header">CMS</li>
                <li class="nav-item"><a href="/cms/articles" class="nav-link {{ Request::is('cms/articles*') ? 'active' : '' }}"><i class="nav-icon fas fa-newspaper"></i>
                        <p>Artikel</p>
                    </a></li>
```

- [ ] **Step 3: Manually verify**

Run: `php artisan serve` (or use the project's existing dev-server workflow), log in, confirm the sidebar shows a "CMS" section with an "Artikel" link, and that visiting any other existing admin page (e.g. `/product`) still renders with no visual regression (the two new `@stack` calls are no-ops when nothing pushes into them).

- [ ] **Step 4: Commit**

```bash
git add resources/views/layout/layout.blade.php resources/views/layout/sidebar.blade.php
git commit -m "Add CMS sidebar section and per-page asset stacks to admin layout"
```

---

### Task 8: Admin views — Artikel list + create/edit form

**Files:**
- Create: `resources/views/cms/articles/index.blade.php`
- Create: `resources/views/cms/articles/form.blade.php`
- Create: `public/js/cms_article_function.js`

**Interfaces:**
- Consumes: routes from Task 6 (`cms.articles.data`, `.store`, `.update`, `.show`, `.destroy`, `.media.destroy`), `@stack` hooks from Task 7.
- Produces: a working browser UI — this task has no automated test (this codebase has no Blade/browser test tooling — see Global Constraints); verification is manual, listed as the final step.

- [ ] **Step 1: Write `resources/views/cms/articles/index.blade.php`**

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
            <div class="col-sm-6"><h1>Artikel</h1></div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        {{-- List view --}}
        <div id="cms-article-list-panel" class="card">
            <div class="card-header">
                <a href="{{ route('cms.articles.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Artikel</a>
            </div>
            <div class="card-body">
                <table id="table-articles" class="table table-bordered table-striped" style="width:100%">
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

        {{-- Create/Edit form, shown instead of the list when the URL is /create or /{id}/edit --}}
        <div id="cms-article-form-panel" class="card" style="display:none;">
            <div class="card-header">
                <h3 class="card-title" id="cms-article-form-title">Tambah Artikel</h3>
            </div>
            <form id="form-article">
                @csrf
                <input type="hidden" id="article_id" name="article_id">
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
                        <label>Isi Artikel</label>
                        <div id="quill-body-editor"></div>
                        <textarea name="body" id="body" style="display:none;"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Kategori (opsional)</label>
                        <input type="text" class="form-control" id="category" name="category">
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
                    <a href="{{ route('cms.articles.index') }}" class="btn btn-default">Batal</a>
                </div>
            </form>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
    window.CMS_ARTICLE_ROUTES = {
        data: "{{ route('cms.articles.data') }}",
        store: "{{ route('cms.articles.store') }}",
        index: "{{ route('cms.articles.index') }}",
        showTemplate: "{{ route('cms.articles.show', ['article' => '__ID__']) }}",
        updateTemplate: "{{ route('cms.articles.update', ['article' => '__ID__']) }}",
        destroyTemplate: "{{ route('cms.articles.destroy', ['article' => '__ID__']) }}",
        mediaDestroyTemplate: "{{ route('cms.articles.media.destroy', ['media' => '__ID__']) }}",
        editTemplate: "{{ route('cms.articles.edit', ['article' => '__ID__']) }}",
        createUrl: "{{ route('cms.articles.create') }}",
    };
    window.CMS_ARTICLE_EDIT_ID = @json(request()->routeIs('cms.articles.edit') ? request()->route('article') : null);
    window.CMS_ARTICLE_IS_CREATE = @json(request()->routeIs('cms.articles.create'));
</script>
<script src="js/cms_article_function.js?v={{ filemtime(public_path('js/cms_article_function.js')) }}"></script>
@endpush
```

- [ ] **Step 2: Write `public/js/cms_article_function.js`**

```javascript
$(function () {
    var routes = window.CMS_ARTICLE_ROUTES;
    var isFormMode = window.CMS_ARTICLE_IS_CREATE || !!window.CMS_ARTICLE_EDIT_ID;
    var quill = null;

    if (isFormMode) {
        $('#cms-article-list-panel').hide();
        $('#cms-article-form-panel').show();
        quill = new Quill('#quill-body-editor', { theme: 'snow' });
    } else {
        initDataTable();
    }

    if (window.CMS_ARTICLE_EDIT_ID) {
        loadArticleForEdit(window.CMS_ARTICLE_EDIT_ID);
    }

    $('#title').on('input', function () {
        if (!window.CMS_ARTICLE_EDIT_ID) {
            $('#slug').val(slugify($(this).val()));
        }
    });

    $('#form-article').on('submit', function (e) {
        e.preventDefault();
        $('#body').val(quill.root.innerHTML);

        var formData = new FormData(this);
        var id = $('#article_id').val();
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
                toastr.success('Artikel berhasil disimpan.');
                window.location.href = routes.index;
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors || {};
                    var firstMessage = Object.values(errors)[0];
                    toastr.error(firstMessage ? firstMessage[0] : 'Data tidak valid.');
                } else {
                    toastr.error('Gagal menyimpan artikel.');
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
        $('#table-articles').DataTable({
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
                            '<button type="button" class="btn btn-sm btn-danger btn-delete-article" data-id="' + id + '"><i class="fas fa-trash"></i></button>';
                    },
                },
            ],
        });
    }

    $(document).on('click', '.btn-delete-article', function () {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Hapus Artikel',
            text: 'Yakin ingin menghapus artikel ini?',
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
                    toastr.success('Artikel dihapus.');
                    $('#table-articles').DataTable().ajax.reload();
                },
                error: function () {
                    toastr.error('Gagal menghapus artikel.');
                },
            });
        });
    });

    function loadArticleForEdit(id) {
        $('#cms-article-form-title').text('Edit Artikel');
        $('#article_id').val(id);

        $.getJSON(routes.showTemplate.replace('__ID__', id), function (article) {
            $('#title').val(article.title);
            $('#slug').val(article.slug);
            $('#excerpt').val(article.excerpt);
            $('#category').val(article.category);
            $('#status').val(article.status);
            $('#show_on_glosspro').prop('checked', !!article.show_on_glosspro);
            $('#show_on_lexent').prop('checked', !!article.show_on_lexent);
            quill.root.innerHTML = article.body || '';

            var $gallery = $('#cms-existing-gallery').empty();
            (article.media || []).forEach(function (media) {
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

- [ ] **Step 3: Manual smoke test — do this whole flow by hand in a browser**

1. `php artisan serve`, log in to the dashboard.
2. Go to CMS → Artikel. The DataTable loads empty (0 records).
3. Click "Tambah Artikel". Fill in a title (confirm the slug field auto-fills), type some body text in the Quill editor, check "GlossPro", pick 2 image files, set status to "Published", submit.
4. Confirm redirect back to the list and the new row appears with a cover thumbnail, correct status/site badge.
5. Click Edit on that row — confirm all fields (including both images and the Quill body) are pre-filled correctly.
6. Remove one image via the × button, confirm it disappears without a page reload and the article is untouched otherwise.
7. Delete the article from the list, confirm the SweetAlert2 prompt, confirm it's gone from the table after confirming.
8. Open `storage/app/public/cms/articles/` on disk and confirm the uploaded files are `.webp`, and that the "thumb" variant is smaller in dimensions than the full one.
9. Hit `GET /api/cms/articles?site=glosspro` with the `X-API-Key` header (e.g. via `curl` or Postman) using the article you just published, confirm it comes back in `data`.

- [ ] **Step 4: Commit**

```bash
git add resources/views/cms/articles/index.blade.php public/js/cms_article_function.js
git commit -m "Add Artikel admin list and create/edit UI"
```

---

## Self-Review Notes

- **Spec coverage:** cms_articles + cms_media schema ✅ (Task 1); site-targeting booleans ✅ (Task 1/2); API-key config-only auth ✅ (Task 3); image resize+WebP pipeline ✅ (Task 4); Artikel API list+detail with pagination shape from spec ✅ (Task 5); admin CRUD + multi-image upload/delete ✅ (Task 6/8); WYSIWYG body editor ✅ (Task 8); sidebar entry ✅ (Task 7). Katalog, Portofolio, and both consumer sites are explicitly out of scope for this plan — see the spec's phased rollout; they get their own plans next.
- **Placeholder scan:** no TBD/TODO; every step has real, complete code.
- **Type consistency:** `Article`/`CmsMedia` field names, `ImageProcessor::process()` return shape (`path`, `thumbnail_path`, `width`, `height`), and the API response keys (`slug`, `title`, `excerpt`, `category`, `published_at`, `cover`, `body`, `media`) are used identically across Tasks 2, 4, 5, 6, and 8 — checked against each other while writing.
