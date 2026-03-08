# Testler

Bu belge, proje test yapısını ve test senaryolarını açıklar.  
**Ana referans:** [PROJECT_STRUCTURE.md](../PROJECT_STRUCTURE.md)

---

## Çalıştırma

```bash
php artisan test
```

veya

```bash
composer test
```

`composer.json` içinde test script’i `--testdox` ile çalışır; test adları okunabilir formatta gösterilir.

---

## Yapı

| Klasör | Açıklama |
|--------|----------|
| tests/Unit/ | Unit testler |
| tests/Feature/ | Feature / HTTP testleri |

---

## Test Dosyaları

### Unit

- **ExampleTest:** Basit `assertTrue(true)` örneği

### Feature

#### DashboardTest

- `test_dashboard_loads_with_empty_database` – Boş DB ile ana sayfa yüklenir, istatistik etiketleri görünür
- `test_dashboard_displays_product_and_visit_stats` – Ürün ve ziyaret istatistikleri doğru
- `test_dashboard_shows_products_with_zero_visits` – Ürün varken 0 ziyaret durumu

#### ProductTest

- `test_product_list_page_loads` – Ürün listesi sayfası
- `test_product_list_displays_products` – Listede ürün görünür
- `test_product_create_page_loads` – Ürün ekleme sayfası
- `test_product_edit_page_loads` – Ürün düzenleme sayfası
- `test_product_can_be_created_via_livewire` – Livewire ile ürün oluşturma
- `test_product_can_be_updated_via_livewire` – Livewire ile güncelleme
- `test_product_can_be_deleted` – Ürün silme
- `test_product_gate_returns_landing` – Gate landing döner
- `test_product_gate_shows_qr_only_when_setting_enabled` – qr_only modunda QR mesajı
- `test_save_visit_creates_visit_with_location` – Ziyaret IP/konum ile kaydedilir
- `test_confirm_enter_sets_session_and_redirects` – confirmEnter session set eder
- `test_product_gate_displays_content_after_enter` – Session ile içerik gösterilir
- `test_full_visit_flow_same_page` – Tam akış (landing → saveVisit → confirmEnter → içerik)
- `test_visit_logs_page_loads` – Ziyaret logları sayfası
- `test_settings_page_loads_and_saves` – Ayarlar sayfası ve kaydetme
- `test_visit_logs_display_visits` – Ziyaret listesinde kayıt görünür
- `test_product_gate_returns_404_for_invalid_token` – Geçersiz token → 404

---

## Test Ortamı

- **phpunit.xml:** `DB_DATABASE=:memory:` (SQLite in-memory)
- **RefreshDatabase:** Feature testlerinde her test öncesi migration çalışır
- **Storage::fake('public'):** ProductTest’te dosya yüklemesi için

---

## Assertion Örnekleri

- `$response->assertStatus(200)`
- `$response->assertSee('metin')`
- `$this->assertDatabaseHas('products', ['name' => '...'])`
- `$this->assertDatabaseCount('product_visits', 1)`
- `Livewire::test('product-create-form')->set(...)->call('save')->assertHasNoErrors()`
