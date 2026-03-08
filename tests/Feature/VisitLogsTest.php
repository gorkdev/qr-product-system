<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ziyaret logları sayfası işlemleri.
 */
class VisitLogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_visit_logs_page_loads(): void
    {
        $response = $this->get(route('visit.index'));

        $response->assertStatus(200);
        $response->assertSee('Ziyaret Logları');
    }

    public function test_visit_logs_display_visits(): void
    {
        $product = Product::factory()->create(['name' => 'Log Ürün']);
        ProductVisit::create([
            'product_id' => $product->id,
            'ip_address' => '192.168.1.1',
            'visited_at' => now(),
        ]);

        $response = $this->get(route('visit.index'));

        $response->assertStatus(200);
        $response->assertSee('Log Ürün');
        $response->assertSee('192.168.1.1');
    }
}
