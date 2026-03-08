<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\ProductVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Ürün ziyaret kaydı işlemleri.
 */
class ProductVisitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_save_visit_creates_visit_with_location(): void
    {
        $product = Product::factory()->create();

        Http::fake([
            'ip-api.com/*' => Http::response([
                'status' => 'success',
                'country' => 'United States',
                'regionName' => 'Virginia',
                'city' => 'Ashburn',
                'timezone' => 'America/New_York',
                'isp' => 'Google LLC',
                'lat' => 39.04,
                'lon' => -77.49,
            ], 200),
        ]);

        $response = $this->postJson(route('product.saveVisit', $product->share_token), [], [
            'Referer' => route('product.gate', $product->share_token),
            'X-Forwarded-For' => '8.8.8.8',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseCount('product_visits', 1);

        $visit = ProductVisit::first();
        $this->assertEquals($product->id, $visit->product_id);
        $this->assertEquals('United States', $visit->country);
        $this->assertEquals('Virginia', $visit->region_name);
        $this->assertEquals('Google LLC', $visit->isp);
        $this->assertEquals('Ashburn', $visit->city);
    }

    public function test_confirm_enter_sets_session_and_redirects(): void
    {
        $product = Product::factory()->create();

        $response = $this->get(route('product.confirmEnter', $product->share_token));

        $response->assertRedirect(route('product.gate', $product->share_token));
        $this->assertTrue(session()->has('product_entered_' . $product->share_token));
    }

    public function test_full_visit_flow_same_page(): void
    {
        $product = Product::factory()->create(['name' => 'Flow Test']);

        $this->get(route('product.gate', $product->share_token))
            ->assertStatus(200)
            ->assertSee('Flow Test')
            ->assertSee('Yönlendiriliyorsunuz');

        $this->postJson(route('product.saveVisit', $product->share_token))
            ->assertOk();

        $this->get(route('product.confirmEnter', $product->share_token))
            ->assertRedirect(route('product.gate', $product->share_token));

        $this->withSession(['product_entered_' . $product->share_token => true])
            ->get(route('product.gate', $product->share_token))
            ->assertStatus(200)
            ->assertSee('Flow Test');
    }

    public function test_save_visit_returns_404_for_invalid_token(): void
    {
        $response = $this->postJson(route('product.saveVisit', 'invalid-token'));

        $response->assertStatus(404);
    }
}
