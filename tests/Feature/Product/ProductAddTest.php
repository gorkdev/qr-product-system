<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Ürün ekleme işlemleri.
 */
class ProductAddTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_product_create_page_loads(): void
    {
        $response = $this->get(route('product.create'));

        $response->assertStatus(200);
        $response->assertSee('Ürün Adı');
        $response->assertSee('Kapak Görseli');
        $response->assertSee('Açıklama');
    }

    public function test_product_can_be_created_via_livewire(): void
    {
        $file = UploadedFile::fake()->image('cover.jpg', 100, 100);

        Livewire::test('product-create-form')
            ->set('name', 'Yeni Test Ürünü')
            ->set('description', 'Bu bir test açıklamasıdır. En az on karakter.')
            ->set('main_image', $file)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['name' => 'Yeni Test Ürünü']);
        $product = Product::where('name', 'Yeni Test Ürünü')->first();
        $this->assertNotNull($product->uuid);
        $this->assertNotNull($product->share_token);
    }

    public function test_product_creation_generates_uuid_and_share_token(): void
    {
        $file = UploadedFile::fake()->image('product.jpg', 200, 200);

        Livewire::test('product-create-form')
            ->set('name', 'UUID Test Ürün')
            ->set('description', 'En az on karakterlik açıklama.')
            ->set('main_image', $file)
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::where('name', 'UUID Test Ürün')->first();
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/i', $product->uuid);
        $this->assertSame(64, strlen($product->share_token));
    }

    public function test_product_creation_requires_name_and_description(): void
    {
        Livewire::test('product-create-form')
            ->set('name', '')
            ->set('description', 'kısa')
            ->call('save')
            ->assertHasErrors(['name', 'description']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_product_creation_requires_main_image(): void
    {
        Livewire::test('product-create-form')
            ->set('name', 'Görselsiz Ürün')
            ->set('description', 'En az on karakterlik açıklama.')
            ->call('save')
            ->assertHasErrors(['main_image']);

        $this->assertDatabaseMissing('products', ['name' => 'Görselsiz Ürün']);
    }

    public function test_product_creation_shows_link_after_save(): void
    {
        $file = UploadedFile::fake()->image('cover.jpg', 100, 100);

        $component = Livewire::test('product-create-form')
            ->set('name', 'Link Test Ürün')
            ->set('description', 'En az on karakterlik açıklama.')
            ->set('main_image', $file)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNotNull($component->get('createdProductLink'));
    }

    public function test_product_creation_saves_qr_path_to_database(): void
    {
        $file = UploadedFile::fake()->image('cover.jpg', 100, 100);

        Livewire::test('product-create-form')
            ->set('name', 'QR Path Test Ürün')
            ->set('description', 'En az on karakterlik açıklama.')
            ->set('main_image', $file)
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::where('name', 'QR Path Test Ürün')->first();
        $this->assertNotNull($product->qr_path);
        $this->assertStringContainsString('products/', $product->qr_path);
        $this->assertStringContainsString('qr.png', $product->qr_path);
    }
}
