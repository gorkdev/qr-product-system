<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Eski QR kodlar ref=qr içermediği için silinir, sonraki erişimde yeniden oluşturulur.
     */
    public function up(): void
    {
        $disk = Storage::disk('public');
        $productsPath = 'products';

        if (! $disk->exists($productsPath)) {
            return;
        }

        foreach ($disk->directories($productsPath) as $productDir) {
            $qrPath = $productDir . '/qr.png';
            if ($disk->exists($qrPath)) {
                $disk->delete($qrPath);
            }
        }
    }

    public function down(): void
    {
        // Geri alınamaz
    }
};
