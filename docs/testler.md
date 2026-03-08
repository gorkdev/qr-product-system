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

`composer.json` içinde test script'i `--testdox` ile çalışır; test adları okunabilir formatta gösterilir.

---

## Yapı

```
tests/
├── Unit/
│   └── ExampleTest.php
└── Feature/
    ├── DashboardTest.php
    ├── DashboardStatsTest.php
    ├── RoutesTest.php
    ├── SettingTest.php
    ├── SettingValidationTest.php
    ├── VisitLogsTest.php
    ├── VisitLogsFilterTest.php
    └── Product/
        ├── ProductAddTest.php
        ├── ProductEditTest.php
        ├── ProductEditUpdateTest.php
        ├── ProductDeleteTest.php
        ├── ProductListTest.php
        ├── ProductListSearchTest.php
        ├── ProductListPaginationTest.php
        ├── ProductValidationTest.php
        ├── ProductMediaTest.php
        ├── ProductGateTest.php
        ├── ProductGateAccessTest.php
        ├── ProductVisitTest.php
        └── ProductVisitRecordingTest.php
```

**Toplam:** 87 test, 189 assertion

---

## Test Dosyaları (Özet)

| Dosya | Test Sayısı | Konu |
|-------|-------------|------|
| DashboardTest | 3 | Boş DB, istatistikler, 0 ziyaret |
| DashboardStatsTest | 3 | Bugün, bu hafta, top 5 |
| RoutesTest | 8 | Tüm ana route'lar |
| SettingTest | 3 | Sayfa, kaydetme |
| SettingValidationTest | 4 | access_mode validasyon |
| VisitLogsTest | 2 | Sayfa, liste |
| VisitLogsFilterTest | 5 | Ürün, cihaz, tarih, arama |
| ProductAddTest | 6 | Ekleme, uuid, validasyon |
| ProductEditTest | 5 | Düzenleme sayfası, güncelleme |
| ProductEditUpdateTest | 3 | Görsel, video güncelleme |
| ProductDeleteTest | 4 | Silme, iptal |
| ProductListTest | 5 | Liste, arama, sıralama |
| ProductListSearchTest | 4 | Arama (boş, partial, yok, sayı) |
| ProductListPaginationTest | 3 | Sayfalama, sıralama |
| ProductValidationTest | 7 | İsim, açıklama, YouTube validasyon |
| ProductMediaTest | 3 | Görsel, video |
| ProductGateTest | 6 | Landing, 404, qr_only, link |
| ProductGateAccessTest | 4 | Erişim modları |
| ProductVisitTest | 4 | saveVisit, confirmEnter, akış |
| ProductVisitRecordingTest | 4 | Çoklu ziyaret, user-agent, hata |

---

## Test Ortamı

- **phpunit.xml:** `DB_DATABASE=:memory:` (SQLite in-memory)
- **RefreshDatabase:** Feature testlerinde her test öncesi migration
- **Storage::fake('public'):** Product testlerinde dosya yüklemesi

---

## Assertion Örnekleri

- `$response->assertStatus(200)`
- `$response->assertSee('metin')`
- `$this->assertDatabaseHas('products', ['name' => '...'])`
- `$this->assertDatabaseCount('product_visits', 1)`
- `Livewire::test('product-create-form')->set(...)->call('save')->assertHasNoErrors()`
