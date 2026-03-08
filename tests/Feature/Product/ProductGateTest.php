<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Ürün gate (public erişim) işlemleri.
 */
class ProductGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_product_gate_returns_landing(): void
    {
        $product = Product::factory()->create(['name' => 'Landing Ürün']);

        $response = $this->get(route('product.gate', $product->share_token));

        $response->assertStatus(200);
        $response->assertSee('Landing Ürün');
    }

    public function test_product_gate_returns_404_for_invalid_token(): void
    {
        $response = $this->get(route('product.gate', 'gecersiz-token-12345'));

        $response->assertStatus(404);
    }

    public function test_product_gate_shows_qr_only_when_setting_enabled(): void
    {
        Setting::set('access_mode', 'qr_only');
        $product = Product::factory()->create();

        $response = $this->get(route('product.gate', $product->share_token));

        $response->assertStatus(200);
        $response->assertSee('QR Kod Gerekli');
    }

    public function test_product_gate_allows_access_with_ref_qr(): void
    {
        Setting::set('access_mode', 'qr_only');
        $product = Product::factory()->create(['name' => 'QR Erişim Ürün']);

        $response = $this->get(route('product.gate', $product->share_token) . '?ref=qr');

        $response->assertStatus(200);
        $response->assertSee('QR Erişim Ürün');
    }

    public function test_product_gate_displays_content_after_enter(): void
    {
        Setting::set('access_mode', 'qr_only');
        $product = Product::factory()->create(['name' => 'Ürün İçeriği']);

        $this->withSession(['product_entered_' . $product->share_token => true])
            ->get(route('product.gate', $product->share_token))
            ->assertStatus(200)
            ->assertSee('Ürün İçeriği');
    }

    public function test_product_gate_link_mode_allows_direct_access(): void
    {
        Setting::set('access_mode', 'link');
        $product = Product::factory()->create(['name' => 'Link Ürün']);

        $response = $this->get(route('product.gate', $product->share_token));

        $response->assertStatus(200);
        $response->assertSee('Link Ürün');
    }
}
