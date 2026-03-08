<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_visits_today_displayed(): void
    {
        $p = Product::factory()->create();
        ProductVisit::create(['product_id' => $p->id, 'ip_address' => '1.1.1.1', 'visited_at' => now()]);
        ProductVisit::create(['product_id' => $p->id, 'ip_address' => '2.2.2.2', 'visited_at' => now()->subDay()]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Bugün');
    }

    public function test_visits_this_week_displayed(): void
    {
        $p = Product::factory()->create();
        ProductVisit::create(['product_id' => $p->id, 'ip_address' => '1.1.1.1', 'visited_at' => now()]);
        ProductVisit::create(['product_id' => $p->id, 'ip_address' => '2.2.2.2', 'visited_at' => now()->subDays(3)]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Bu Hafta');
    }

    public function test_top_products_limited_to_5(): void
    {
        $products = Product::factory()->count(7)->create();
        foreach ($products as $i => $p) {
            ProductVisit::create(['product_id' => $p->id, 'ip_address' => "1.1.1.$i", 'visited_at' => now()]);
        }
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
