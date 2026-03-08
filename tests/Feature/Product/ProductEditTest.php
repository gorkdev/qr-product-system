<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Ürün düzenleme işlemleri.
 */
class ProductEditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_product_edit_page_loads(): void
    {
        $product = Product::factory()->create(['name' => 'Düzenlenecek Ürün']);

        $response = $this->get(route('product.edit', $product->uuid));

        $response->assertStatus(200);
        $response->assertSee('Ürünü Düzenle');
        $response->assertSee('Ürün Adı');
    }

    public function test_product_edit_page_returns_404_for_invalid_uuid(): void
    {
        $response = $this->get(route('product.edit', '00000000-0000-0000-0000-000000000000'));

        $response->assertStatus(404);
    }

    public function test_product_can_be_updated_via_livewire(): void
    {
        $product = Product::factory()->create(['name' => 'Eski İsim']);

        Livewire::test('product-create-form', ['productId' => $product->uuid])
            ->set('name', 'Güncel İsim')
            ->set('description', 'Güncellenmiş açıklama metni.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['name' => 'Güncel İsim']);
        $this->assertDatabaseMissing('products', ['name' => 'Eski İsim']);
    }

    public function test_product_update_preserves_uuid_and_share_token(): void
    {
        $product = Product::factory()->create();
        $originalUuid = $product->uuid;
        $originalToken = $product->share_token;

        Livewire::test('product-create-form', ['productId' => $product->uuid])
            ->set('name', 'Yeni İsim')
            ->set('description', 'Güncellenmiş açıklama.')
            ->call('save')
            ->assertHasNoErrors();

        $product->refresh();
        $this->assertSame($originalUuid, $product->uuid);
        $this->assertSame($originalToken, $product->share_token);
    }

    public function test_product_update_validates_required_fields(): void
    {
        $product = Product::factory()->create();

        Livewire::test('product-create-form', ['productId' => $product->uuid])
            ->set('name', '')
            ->set('description', 'kısa')
            ->call('save')
            ->assertHasErrors(['name', 'description']);
    }
}
