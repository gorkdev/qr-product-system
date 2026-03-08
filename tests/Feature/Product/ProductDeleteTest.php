<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Ürün silme işlemleri.
 */
class ProductDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_product_can_be_deleted(): void
    {
        $product = Product::factory()->create();

        Livewire::test('product-list')
            ->call('confirmDelete', $product->id)
            ->call('deleteProduct');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_product_delete_removes_from_list(): void
    {
        $product = Product::factory()->create(['name' => 'Silinecek Ürün']);

        Livewire::test('product-list')
            ->call('confirmDelete', $product->id)
            ->call('deleteProduct');

        $response = $this->get(route('product.index'));
        $response->assertDontSee('Silinecek Ürün');
    }

    public function test_cancel_delete_keeps_product(): void
    {
        $product = Product::factory()->create(['name' => 'Korunacak Ürün']);

        Livewire::test('product-list')
            ->call('confirmDelete', $product->id)
            ->call('cancelDelete');

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_delete_product_without_confirm_does_nothing(): void
    {
        $product = Product::factory()->create();

        Livewire::test('product-list')->call('deleteProduct');

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
}
