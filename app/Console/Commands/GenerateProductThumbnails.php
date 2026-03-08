<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Exceptions\DriverException;
use Intervention\Image\Laravel\Facades\Image;

class GenerateProductThumbnails extends Command
{
    protected $signature = 'products:generate-thumbnails';

    protected $description = 'Mevcut ürün görselleri için 128x128 thumbnail oluşturur.';

    public function handle(): int
    {
        $products = Product::whereNotNull('images')->get();
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            $images = $product->images ?? [];
            foreach ($images as $url) {
                $path = $this->urlToPath($url);
                if (!$path) continue;

                $fullPath = Storage::disk('public')->path($path);
                $dir = dirname($path);
                $file = basename($path);
                $thumbPath = $dir . '/thumbs/' . $file;
                $thumbFullPath = Storage::disk('public')->path($thumbPath);

                if (!file_exists($fullPath) || file_exists($thumbFullPath)) {
                    continue;
                }

                try {
                    Storage::disk('public')->makeDirectory($dir . '/thumbs/');
                    $img = Image::read($fullPath);
                    $img->cover(128, 128)->save($thumbFullPath, quality: 85);
                } catch (DriverException|\Throwable $e) {
                    $this->newLine();
                    $this->warn("Atlandı ({$product->name}): " . $e->getMessage());
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Thumbnail oluşturma tamamlandı.');

        return Command::SUCCESS;
    }

    private function urlToPath(?string $url): ?string
    {
        if (!$url) return null;
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path || !str_contains($path, '/storage/')) return null;
        return ltrim(preg_replace('#^/storage/#', '', $path), '/');
    }
}
