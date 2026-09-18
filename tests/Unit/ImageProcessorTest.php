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
