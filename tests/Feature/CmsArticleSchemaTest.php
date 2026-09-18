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
