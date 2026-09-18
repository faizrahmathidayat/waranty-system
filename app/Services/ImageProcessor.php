<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        Storage::disk('public')->put($relativePath, (string) $image->encode('webp', $quality));
    }
}
