<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_dashboard_route(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_product_index_route(): void
    {
        $this->get(route('product.index'))->assertStatus(200);
    }

    public function test_product_create_route(): void
    {
        $this->get(route('product.create'))->assertStatus(200);
    }

    public function test_product_edit_route_with_valid_uuid(): void
    {
        $p = Product::factory()->create();
        $this->get(route('product.edit', $p->uuid))->assertStatus(200);
    }

    public function test_visit_index_route(): void
    {
        $this->get(route('visit.index'))->assertStatus(200);
    }

    public function test_setting_index_route(): void
    {
        $this->get(route('setting.index'))->assertStatus(200);
    }

    public function test_product_gate_route_with_valid_token(): void
    {
        $p = Product::factory()->create();
        $this->get(route('product.gate', $p->share_token))->assertStatus(200);
    }

    public function test_confirm_enter_redirects(): void
    {
        $p = Product::factory()->create();
        $this->get(route('product.confirmEnter', $p->share_token))->assertRedirect();
    }
}
