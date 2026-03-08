# Ürün Listesi

Bu belge, admin panelindeki ürün listesi sayfasını ve işlemlerini açıklar.  
**Ana referans:** [PROJECT_STRUCTURE.md](../PROJECT_STRUCTURE.md)

---

## Genel Bakış

- **URL:** `/urunler` → `product.index` route
- **View:** `admin.product-index` → Livewire `product-list` bileşeni

---

## Livewire Bileşeni: ProductList

**Dosya:** `app/Livewire/ProductList.php`

### Özellikler

| Özellik | Tip | Açıklama |
|---------|-----|----------|
| search | string | Ürün adına göre arama (`LIKE %...%`) |
| sortBy | string | created_at veya visits_count |
| sortDir | string | asc / desc |
| deleteProductId | int\|null | Silinecek ürün ID (onay için) |

### Metodlar

- `updatingSearch()` – Arama değişince sayfa 1’e döner
- `confirmDelete($id)` – Silme onayı için modal açar
- `cancelDelete()` – Onayı iptal eder
- `deleteProduct()` – Ürünü siler (Product::where('id', $id)->delete())

---

## Sorgu Yapısı

```php
Product::query()
    ->select(['id', 'uuid', 'name', 'images', 'share_token', 'created_at', 'updated_at'])
    ->withCount('visits')
    ->when($search !== '', fn ($q) => $q->where('name', 'like', '%' . $search . '%'))
    ->orderBy($sortBy === 'visits_count' ? 'visits_count' : $sortBy, $sortDir)
    ->paginate(10)
    ->withQueryString();
```

---

## Her Ürün İçin Hesaplanan Değerler

`access_mode` ayarına göre:

- **link:** `product_link` = `url(route('product.gate', share_token))`
- **qr_only:** `product_link` = `url(...)?ref=qr`
- `qr_url` = `$product->getQrCodePath()` (QR görseli)

---

## View Yapısı

**Dosya:** `resources/views/livewire/product-list.blade.php`

- Arama input
- Sıralama seçenekleri (ad, oluşturulma, ziyaret sayısı)
- Tablo: Thumbnail, Ad, Ziyaret, Link, QR, Aksiyonlar
- Silme onay modal

---

## Silme İşlemi

1. Kullanıcı "Sil" tıklar → `confirmDelete($product->id)`
2. Modal açılır
3. Onaylanırsa `deleteProduct()` çağrılır
4. `Product::delete()` – Model `deleting` event’i ile `Storage::disk('public')->deleteDirectory($product->getStoragePath())` çalışır
5. Tüm ürün klasörü (görseller, PDF, QR) silinir

---

## İlgili Dosyalar

| Dosya | Açıklama |
|-------|----------|
| `app/Livewire/ProductList.php` | Livewire bileşeni |
| `resources/views/livewire/product-list.blade.php` | Şablon |
| `resources/views/admin/product-index.blade.php` | Sayfa layout |
