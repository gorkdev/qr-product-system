<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\ProductVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductVisitRecordingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_multiple_visits_same_product(): void
    {
        $p = Product::factory()->create();
        Http::fake(['ip-api.com/*' => Http::response(['status' => 'success', 'city' => 'Istanbul', 'country' => 'Turkey'], 200)]);

        $this->postJson(route('product.saveVisit', $p->share_token), [], ['X-Forwarded-For' => '8.8.8.8']);
        $this->postJson(route('product.saveVisit', $p->share_token), [], ['X-Forwarded-For' => '1.2.3.4']);

        $this->assertDatabaseCount('product_visits', 2);
        $this->assertEquals(2, $p->visits()->count());
    }

    public function test_visit_stores_user_agent(): void
    {
        $p = Product::factory()->create();
        Http::fake(['ip-api.com/*' => Http::response(['status' => 'success'], 200)]);

        $this->postJson(route('product.saveVisit', $p->share_token), [], [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0',
        ]);

        $visit = ProductVisit::first();
        $this->assertStringContainsString('Chrome', $visit->browser ?? '');
    }

    public function test_save_visit_succeeds_even_with_geo_failure(): void
    {
        $p = Product::factory()->create();
        Http::fake(['ip-api.com/*' => Http::response([], 500)]);

        $response = $this->postJson(route('product.saveVisit', $p->share_token), [], ['X-Forwarded-For' => '127.0.0.1']);

        $response->assertOk();
        $this->assertDatabaseCount('product_visits', 1);
    }

    public function test_confirm_enter_via_ajax_returns_json(): void
    {
        $p = Product::factory()->create();

        $response = $this->postJson(route('product.confirmEnter', $p->share_token), [], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
