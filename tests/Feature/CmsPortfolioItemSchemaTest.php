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
