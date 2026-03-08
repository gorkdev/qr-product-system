<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Dashboard (Ana Sayfa) testleri.
 * Adım adım: HTTP isteği, yanıt doğrulama, istatistik ve görünüm kontrolü.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Boş veritabanı ile dashboard yüklenmeli
     * 2. Sayfa 200 dönmeli
     * 3. "Toplam Ürün", "Toplam Ziyaret" metinleri görünmeli
     * 4. Ürün sayısı 0 olmalı
     */
    public function test_dashboard_loads_with_empty_database(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Toplam Ürün');
        $response->assertSee('Toplam Ziyaret');
        $response->assertSee('Bugün');
        $response->assertSee('Bu Hafta');
        $response->assertSee('Bu Ay');
        $response->assertSee('0', false); // Ürün ve ziyaret sayıları 0
    }

    /**
     * 1. Ürünler ve ziyaretler oluşturulmalı
     * 2. Dashboard istatistikleri doğru göstermeli
     * 3. "En Çok Ziyaret Edilen Ürünler" tablosu görünmeli
     */
    public function test_dashboard_displays_product_and_visit_stats(): void
    {
        $product1 = Product::factory()->create(['name' => 'En Popüler Ürün']);
        $product2 = Product::factory()->create(['name' => 'İkinci Ürün']);

        ProductVisit::create([
            'product_id' => $product1->id,
            'ip_address' => '1.2.3.4',
            'visited_at' => now(),
        ]);
        ProductVisit::create([
            'product_id' => $product1->id,
            'ip_address' => '5.6.7.8',
            'visited_at' => now(),
        ]);
        ProductVisit::create([
            'product_id' => $product2->id,
            'ip_address' => '9.10.11.12',
            'visited_at' => now(),
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('2');      // Toplam ürün
        $response->assertSee('3');      // Toplam ziyaret
        $response->assertSee('En Popüler Ürün');
        $response->assertSee('İkinci Ürün');
        $response->assertSee('En Çok Ziyaret Edilen Ürünler');
        $response->assertSee('Düzenle');
    }

    /**
     * 1. Ürün var ama hiç ziyaret yok
     * 2. Dashboard ürün istatistiğini (1) göstermeli
     * 3. Ziyaret sayıları 0 olmalı, ürün "En Çok Ziyaret Edilen" tablosunda 0 ziyaretle listelenmeli
     */
    public function test_dashboard_shows_products_with_zero_visits(): void
    {
        Product::factory()->create(['name' => 'Ziyaret Edilmemiş']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('1'); // Toplam ürün
        $response->assertSee('0', false); // Ziyaret sayıları 0
        $response->assertSee('Ziyaret Edilmemiş');
    }
}
