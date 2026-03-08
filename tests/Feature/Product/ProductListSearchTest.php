<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductListSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_search_empty_returns_all(): void
    {
        Product::factory()->create(['name' => 'Ürün A']);
        Product::factory()->create(['name' => 'Ürün B']);
        $c = Livewire::test('product-list')->set('search', '');
        $c->assertSee('Ürün A');
        $c->assertSee('Ürün B');
    }

    public function test_search_partial_match(): void
    {
        Product::factory()->create(['name' => 'Kablosuz Kulaklık Pro']);
        $c = Livewire::test('product-list')->set('search', 'Kulaklık');
        $c->assertSee('Kablosuz Kulaklık Pro');
    }

    public function test_search_no_results(): void
    {
        Product::factory()->create(['name' => 'Başka Ürün']);
        $c = Livewire::test('product-list')->set('search', 'BulunamayanXyZ123');
        $c->assertDontSee('Başka Ürün');
    }

    public function test_search_with_numbers_in_name(): void
    {
        Product::factory()->create(['name' => 'Ürün 2024 Pro']);
        $c = Livewire::test('product-list')->set('search', '2024');
        $c->assertSee('Ürün 2024 Pro');
    }
}
