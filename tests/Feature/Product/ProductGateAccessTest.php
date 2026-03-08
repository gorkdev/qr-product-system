<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductGateAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_qr_only_without_ref_shows_qr_message(): void
    {
        Setting::set('access_mode', 'qr_only');
        $p = Product::factory()->create();
        $response = $this->get(route('product.gate', $p->share_token));
        $response->assertSee('QR Kod Gerekli');
    }

    public function test_qr_only_with_ref_qr_shows_landing(): void
    {
        Setting::set('access_mode', 'qr_only');
        $p = Product::factory()->create(['name' => 'QR Erişim']);
        $response = $this->get(route('product.gate', $p->share_token) . '?ref=qr');
        $response->assertSee('QR Erişim');
    }

    public function test_link_mode_without_ref_allowed(): void
    {
        Setting::set('access_mode', 'link');
        $p = Product::factory()->create(['name' => 'Link Erişim']);
        $response = $this->get(route('product.gate', $p->share_token));
        $response->assertSee('Link Erişim');
    }

    public function test_link_mode_with_ref_qr_allowed(): void
    {
        Setting::set('access_mode', 'link');
        $p = Product::factory()->create(['name' => 'Ref QR']);
        $response = $this->get(route('product.gate', $p->share_token) . '?ref=qr');
        $response->assertSee('Ref QR');
    }
}
