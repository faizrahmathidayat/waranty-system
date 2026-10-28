<?php

namespace Tests\Feature\Cms;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Article;
use App\Models\CatalogItem;
use App\Models\Login;
use App\Models\PortfolioItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsSiteFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->actingAs(Login::factory()->create());
    }

    public function listProvider(): array
    {
        return [
            'artikel' => [Article::class, '/cms/articles/data'],
            'katalog' => [CatalogItem::class, '/cms/catalog/data'],
            'portofolio' => [PortfolioItem::class, '/cms/portfolio/data'],
        ];
    }

    /** @dataProvider listProvider */
    public function test_list_can_be_filtered_by_site(string $model, string $url): void
    {
        foreach ([['gp', true, false], ['lx', false, true], ['both', true, true], ['none', false, false]] as [$slug, $glosspro, $lexent]) {
            $model::create(['title' => $slug, 'slug' => $slug, 'body' => 'x', 'status' => 'published', 'published_at' => now(), 'show_on_glosspro' => $glosspro, 'show_on_lexent' => $lexent]);
        }
        $titles = fn ($query) => collect($this->getJson($url . $query)->assertOk()->json('data'))->pluck('title')->sort()->values()->all();

        $this->assertSame(['both', 'gp', 'lx', 'none'], $titles(''));
        $this->assertSame(['both', 'gp'], $titles('?site=glosspro'));
        $this->assertSame(['both', 'lx'], $titles('?site=lexent'));
        $this->assertSame(['both', 'gp', 'lx', 'none'], $titles('?site=bogus'));
    }
}
