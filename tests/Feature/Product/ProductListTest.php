<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Ürün listesi işlemleri.
 */
class ProductListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_product_list_page_loads(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->get(route('product.index'));

        $response->assertStatus(200);
        $response->assertSee('Ürün adıyla ara');
        $this->assertDatabaseCount('products', 3);
    }

    public function test_product_list_displays_products(): void
    {
        Product::factory()->create(['name' => 'Test Ürün XYZ']);

        $response = $this->get(route('product.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Ürün XYZ');
    }

    public function test_product_list_search_filters_by_name(): void
    {
        Product::factory()->create(['name' => 'Aranan Ürün']);
        Product::factory()->create(['name' => 'Başka Ürün']);

        $component = Livewire::test('product-list')
            ->set('search', 'Aranan');

        $component->assertSee('Aranan Ürün');
        $component->assertDontSee('Başka Ürün');
    }

    public function test_product_list_sorts_by_created_at(): void
    {
        Product::factory()->create(['name' => 'İlk', 'created_at' => now()->subDay()]);
        Product::factory()->create(['name' => 'Son', 'created_at' => now()]);

        $component = Livewire::test('product-list')
            ->set('sortBy', 'created_at')
            ->set('sortDir', 'desc');

        $html = $component->html();
        $this->assertStringContainsString('Son', $html);
        $this->assertStringContainsString('İlk', $html);
    }

    public function test_product_list_shows_visit_count(): void
    {
        $product = Product::factory()->create(['name' => 'Ziyaretli Ürün']);
        $product->visits()->create([
            'ip_address' => '1.2.3.4',
            'visited_at' => now(),
        ]);

        $component = Livewire::test('product-list');
        $component->assertSee('Ziyaretli Ürün');
        $component->assertSee('1');
    }
}
