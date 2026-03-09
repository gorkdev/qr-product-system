<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class EnsureProductQrCodes extends Command
{
    protected $signature = 'products:ensure-qr-codes';

    protected $description = 'Tüm ürünler için QR kod oluşturur veya yeniler.';

    public function handle(): int
    {
        $products = Product::all();
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        $failed = 0;
        foreach ($products as $product) {
            try {
                $product->ensureQrCodeExists();
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->error("{$product->name}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        if ($failed > 0) {
            $this->warn("{$failed} ürün başarısız oldu.");
        } else {
            $this->info('QR kod işlemi tamamlandı.');
        }

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
