<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = ['name', 'description', 'images', 'videos', 'pdf_path', 'share_token'];

    protected $casts = [
        'images' => 'array',
        'videos' => 'array',
    ];

    /**
     * Get storage base path: products/{uuid}/
     */
    public function getStoragePath(): string
    {
        return 'products/' . $this->uuid . '/';
    }

    /**
     * Get images path: products/{uuid}/images/
     */
    public function getImagesPath(): string
    {
        return $this->getStoragePath() . 'images/';
    }

    /**
     * Get PDF path: products/{uuid}/pdf/
     */
    public function getPdfPath(): string
    {
        return $this->getStoragePath() . 'pdf/';
    }

    /**
     * Listelerde kullanılmak üzere 128x128 thumbnail URL (yoksa ilk görsel)
     */
    public function getMainThumbnailAttribute(): ?string
    {
        $images = $this->images ?? [];
        $first = $images[0] ?? null;
        return $first ? $this->thumbnailUrlFromFull($first) : null;
    }

    /**
     * Full image URL'den thumb URL türet (images/x.jpg -> images/thumbs/x.jpg)
     */
    public function thumbnailUrlFromFull(string $fullUrl): string
    {
        $path = parse_url($fullUrl, PHP_URL_PATH);
        if (!$path || !str_contains($path, '/storage/')) {
            return $fullUrl;
        }
        $relative = preg_replace('#^/storage/#', '', $path);
        $dir = dirname($relative);
        $file = basename($relative);
        $thumbPath = $dir . '/thumbs/' . $file;
        return Storage::url($thumbPath);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function booted()
    {
        static::creating(function ($product) {
            $product->uuid = (string) Str::uuid();
            $product->share_token = $product->share_token ?? Str::random(64);
        });

        static::deleting(function ($product) {
            Storage::disk('public')->deleteDirectory($product->getStoragePath());
        });
    }
}
