<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VisitLogsFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_by_product(): void
    {
        $a = Product::factory()->create(['name' => 'Ürün A']);
        $b = Product::factory()->create(['name' => 'Ürün B']);
        ProductVisit::create(['product_id' => $a->id, 'ip_address' => '1.1.1.1', 'visited_at' => now()]);
        ProductVisit::create(['product_id' => $b->id, 'ip_address' => '2.2.2.2', 'visited_at' => now()]);

        $c = Livewire::test('visit-list')->set('productFilter', (string) $a->id);
        $visits = $c->viewData('visits');
        $this->assertCount(1, $visits->items());
        $this->assertEquals($a->id, $visits->first()->product_id);
    }

    public function test_filter_by_device(): void
    {
        $p = Product::factory()->create();
        ProductVisit::create(['product_id' => $p->id, 'device_type' => 'Mobil', 'ip_address' => '1.1.1.1', 'visited_at' => now()]);
        ProductVisit::create(['product_id' => $p->id, 'device_type' => 'Masaüstü', 'ip_address' => '2.2.2.2', 'visited_at' => now()]);

        $c = Livewire::test('visit-list')->set('deviceFilter', 'Mobil');
        $visits = $c->viewData('visits');
        $this->assertCount(1, $visits->items());
        $this->assertEquals('Mobil', $visits->first()->device_type);
    }

    public function test_filter_by_date_range(): void
    {
        $p = Product::factory()->create();
        ProductVisit::create(['product_id' => $p->id, 'ip_address' => '1.1.1.1', 'visited_at' => now()->subDays(2)]);
        ProductVisit::create(['product_id' => $p->id, 'ip_address' => '2.2.2.2', 'visited_at' => now()]);

        $c = Livewire::test('visit-list')
            ->set('dateFrom', now()->subDay()->format('Y-m-d'))
            ->set('dateTo', now()->format('Y-m-d'));
        $visits = $c->viewData('visits');
        $this->assertCount(1, $visits->items());
    }

    public function test_filter_by_search_ip(): void
    {
        $p = Product::factory()->create();
        ProductVisit::create(['product_id' => $p->id, 'ip_address' => '192.168.1.100', 'visited_at' => now()]);

        $c = Livewire::test('visit-list')->set('search', '192.168');
        $visits = $c->viewData('visits');
        $this->assertCount(1, $visits->items());
    }

    public function test_filter_by_search_country(): void
    {
        $p = Product::factory()->create();
        ProductVisit::create(['product_id' => $p->id, 'ip_address' => '1.1.1.1', 'country' => 'Turkey', 'visited_at' => now()]);

        $c = Livewire::test('visit-list')->set('search', 'Turkey');
        $visits = $c->viewData('visits');
        $this->assertCount(1, $visits->items());
    }
}
