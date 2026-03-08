<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVisit;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Ürün yönetimi ve ziyaret akışı testleri.
 * Her test adım adım: hazırlık, işlem, doğrulama.
 */
class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Adım 1: 3 ürün oluştur
     * Adım 2: Ürün listesi sayfasına GET isteği
     * Adım 3: 200 OK, arama alanı görünür
     */
    public function test_product_list_page_loads(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->get(route('product.index'));

        $response->assertStatus(200);
        $response->assertSee('Ürün adıyla ara');
        $this->assertDatabaseCount('products', 3);
    }

    /**
     * Adım 1: Belirli isimle ürün oluştur
     * Adım 2: Liste sayfasını yükle
     * Adım 3: Ürün adı sayfada görünmeli
     */
    public function test_product_list_displays_products(): void
    {
        $product = Product::factory()->create(['name' => 'Test Ürün XYZ']);

        $response = $this->get(route('product.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Ürün XYZ');
    }

    /**
     * Adım 1: Ürün ekleme sayfasına GET
     * Adım 2: Form alanları görünmeli (Ürün Adı vb.)
     */
    public function test_product_create_page_loads(): void
    {
        $response = $this->get(route('product.create'));

        $response->assertStatus(200);
        $response->assertSee('Ürün Adı');
    }

    /**
     * Adım 1: Ürün oluştur
     * Adım 2: Düzenleme sayfasına UUID ile GET
     * Adım 3: Ürün adı formda görünmeli
     */
    public function test_product_edit_page_loads(): void
    {
        $product = Product::factory()->create();

        $response = $this->get(route('product.edit', $product->uuid));

        $response->assertStatus(200);
        $response->assertSee($product->name);
    }

    /**
     * Adım 1: Livewire form ile ad, açıklama, resim gönder
     * Adım 2: save() çağrısı hata vermemeli
     * Adım 3: Veritabanında ürün kaydı olmalı
     */
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
    }

    /**
     * Adım 1: Mevcut ürün oluştur
     * Adım 2: Livewire ile isim ve açıklama güncelle
     * Adım 3: Veritabanında güncel isim kayıtlı olmalı
     */
    public function test_product_can_be_updated_via_livewire(): void
    {
        $product = Product::factory()->create(['name' => 'Eski İsim']);

        Livewire::test('product-create-form', ['productId' => $product->uuid])
            ->set('name', 'Güncel İsim')
            ->set('description', 'Güncellenmiş açıklama metni.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['name' => 'Güncel İsim']);
    }

    /**
     * Adım 1: Ürün oluştur
     * Adım 2: Silme onayı ve deleteProduct çağrısı
     * Adım 3: Ürün veritabanından silinmiş olmalı
     */
    public function test_product_can_be_deleted(): void
    {
        $product = Product::factory()->create();

        Livewire::test('product-list')
            ->call('confirmDelete', $product->id)
            ->call('deleteProduct');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /**
     * Adım 1: Ürün oluştur (share_token ile)
     * Adım 2: urun-bilgisi/{token} sayfasına GET
     * Adım 3: Ürün adı landinge görünmeli
     */
    public function test_product_gate_returns_landing(): void
    {
        $product = Product::factory()->create();

        $response = $this->get(route('product.gate', $product->share_token));

        $response->assertStatus(200);
        $response->assertSee($product->name);
    }

    /**
     * Adım 1: access_mode = qr_only ayarla
     * Adım 2: Gate sayfasına GET
     * Adım 3: "QR Kod Gerekli" mesajı görünmeli
     */
    public function test_product_gate_shows_qr_only_when_setting_enabled(): void
    {
        Setting::set('access_mode', 'qr_only');
        $product = Product::factory()->create();

        $response = $this->get(route('product.gate', $product->share_token));

        $response->assertStatus(200);
        $response->assertSee('QR Kod Gerekli');
    }

    /**
     * Adım 1: IP-API fake response hazırla
     * Adım 2: saveVisit POST isteği (Referer, X-Forwarded-For)
     * Adım 3: Ziyaret kaydı oluşmalı, ülke/şehir/ISP doğru
     */
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

    /**
     * Adım 1: confirmEnter GET isteği
     * Adım 2: Gate sayfasına yönlendirme ve session flag set edilmeli
     */
    public function test_confirm_enter_sets_session_and_redirects(): void
    {
        $product = Product::factory()->create();

        $response = $this->get(route('product.confirmEnter', $product->share_token));

        $response->assertRedirect(route('product.gate', $product->share_token));
        $this->assertTrue(session()->has('product_entered_' . $product->share_token));
    }

    /**
     * Adım 1: qr_only modunda ürün oluştur
     * Adım 2: Session ile "giriş yapılmış" simüle et
     * Adım 3: Gate sayfası içeriği (ürün adı) görünmeli
     */
    public function test_product_gate_displays_content_after_enter(): void
    {
        Setting::set('access_mode', 'qr_only');
        $product = Product::factory()->create(['name' => 'Ürün İçeriği']);

        $this->withSession(['product_entered_' . $product->share_token => true])
            ->get(route('product.gate', $product->share_token))
            ->assertStatus(200)
            ->assertSee('Ürün İçeriği');
    }

    /**
     * Tam akış: Landing -> saveVisit -> confirmEnter -> içerik
     * Her adımda beklenen yanıt doğrulanır
     */
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

    /**
     * Adım 1: Ziyaret logları sayfasına GET
     * Adım 2: "Ziyaret Logları" başlığı görünmeli
     */
    public function test_visit_logs_page_loads(): void
    {
        $response = $this->get(route('visit.index'));

        $response->assertStatus(200);
        $response->assertSee('Ziyaret Logları');
    }

    /**
     * Adım 1: Ayarlar sayfasını yükle, erişim modu ve Kaydet butonunu kontrol et
     * Adım 2: access_mode POST ile güncelle
     * Adım 3: Yönlendirme ve Setting::get ile değerin kaydedildiğini doğrula
     */
    public function test_settings_page_loads_and_saves(): void
    {
        $response = $this->get(route('setting.index'));

        $response->assertStatus(200);
        $response->assertSee('Yönlendirme sitesi erişimi');
        $response->assertSee('Kaydet');

        $response = $this->post(route('setting.update'), ['access_mode' => 'qr_only']);
        $response->assertRedirect(route('setting.index'));
        $this->assertEquals('qr_only', Setting::get('access_mode'));
    }

    /**
     * Adım 1: Ürün ve ziyaret kaydı oluştur
     * Adım 2: Ziyaret logları sayfasını yükle
     * Adım 3: Ürün adı ve IP adresi görünmeli
     */
    public function test_visit_logs_display_visits(): void
    {
        $product = Product::factory()->create();
        ProductVisit::create([
            'product_id' => $product->id,
            'ip_address' => '192.168.1.1',
            'visited_at' => now(),
        ]);

        $response = $this->get(route('visit.index'));

        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee('192.168.1.1');
    }

    /**
     * Geçersiz share_token ile gate sayfası 404 dönmeli
     */
    public function test_product_gate_returns_404_for_invalid_token(): void
    {
        $response = $this->get(route('product.gate', 'gecersiz-token-12345'));

        $response->assertStatus(404);
    }
}
