<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductListPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_first_page_shows_10_products(): void
    {
        Product::factory()->count(15)->create();
        $c = Livewire::test('product-list');
        $products = $c->viewData('products');
        $this->assertCount(10, $products->items());
    }

    public function test_has_multiple_pages_when_more_than_10(): void
    {
        Product::factory()->count(12)->create();
        $c = Livewire::test('product-list');
        $products = $c->viewData('products');
        $this->assertTrue($products->hasPages());
    }

    public function test_sort_by_visits_count(): void
    {
        $a = Product::factory()->create(['name' => 'Az Ziyaret']);
        $b = Product::factory()->create(['name' => 'Çok Ziyaret']);
        $b->visits()->create(['ip_address' => '1.1.1.1', 'visited_at' => now()]);
        $b->visits()->create(['ip_address' => '2.2.2.2', 'visited_at' => now()]);

        $c = Livewire::test('product-list')
            ->set('sortBy', 'visits_count')
            ->set('sortDir', 'desc');
        $html = $c->html();
        $posB = strpos($html, 'Çok Ziyaret');
        $posA = strpos($html, 'Az Ziyaret');
        $this->assertLessThan($posA, $posB);
    }
}
