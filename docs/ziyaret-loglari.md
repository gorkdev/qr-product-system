# Ziyaret Logları

Bu belge, admin panelindeki ziyaret logları sayfasını açıklar.  
**Ana referans:** [PROJECT_STRUCTURE.md](../PROJECT_STRUCTURE.md)

---

## Genel Bakış

- **URL:** `/ziyaretler` → `visit.index`
- **Route:** Closure – `view('admin.visit-index')` döner
- **Livewire:** `visit-list` bileşeni

---

## Livewire: VisitList

**Dosya:** `app/Livewire/VisitList.php`

### Filtreler

| Özellik | Tip | Açıklama |
|---------|-----|----------|
| search | string | ip_address, user_agent, browser, device_type, city, country, region_name, error_message |
| productFilter | string | product_id |
| deviceFilter | string | device_type |
| dateFrom | string | visited_at >= |
| dateTo | string | visited_at <= |
| dateRange | string | today, week, month, quarter, year (preset) |

### Sorgu

```php
ProductVisit::with('product')
    ->when($search, ...)  // OR ile çoklu alan araması
    ->when($productFilter, ...)
    ->when($deviceFilter, ...)
    ->when($dateFrom, ...)
    ->when($dateTo, ...)
    ->orderByDesc('visited_at')
    ->paginate(20)
    ->withQueryString();
```

---

## View: livewire.visit-list

- Arama input
- Ürün filtresi (dropdown)
- Cihaz filtresi (Mobil, Tablet, Masaüstü)
- Tarih aralığı preset (Bugün, Hafta, Ay, Çeyrek, Yıl)
- Manuel dateFrom, dateTo
- Tablo: Tarih, Ürün, IP, Cihaz, Tarayıcı, Konum, Hata (varsa)
- Toplam ziyaret sayısı

---

## İlgili Dokümanlar

- [ziyaret-takibi.md](ziyaret-takibi.md) – Ziyaret kaydı yapısı
- [veritabani.md](veritabani.md) – product_visits tablosu
