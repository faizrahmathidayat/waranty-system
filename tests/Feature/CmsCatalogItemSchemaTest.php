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
