# Ürün Düzenleme

Bu belge, mevcut ürünün güncellenmesi sürecini açıklar.  
**Ana referans:** [PROJECT_STRUCTURE.md](../PROJECT_STRUCTURE.md)

---

## Genel Bakış

- **URL:** `/urunler/{uuid}/duzenle` → `product.edit` route
- **Route model binding:** `Product` model `uuid` ile resolve edilir
- **Form:** Ürün ekleme ile aynı Livewire bileşeni, `productId` parametresi ile

---

## Eklede vs Düzenlemede Farklar

| Özellik | Yeni Ürün | Düzenleme |
|---------|-----------|------------|
| productId | null | UUID |
| Kapak görseli | Zorunlu | Opsiyonel (mevcut korunur) |
| mount | Boş form | Mevcut veriler doldurulur |
| save sonrası | Form sıfırlanır, link gösterilir | Sadece dosya alanları sıfırlanır |

---

## Mount Akışı (Düzenleme)

```php
if ($this->isEdit) {
    $this->product = Product::where('uuid', $productId)->firstOrFail();
    $this->name = $this->product->name;
    $this->description = $this->product->description ?? '';
    $this->videos = array_merge($this->product->videos ?? [], ['']);
}
```

---

## Güncelleme Akışı (save)

1. Mevcut `$product` kullanılır, yeni kayıt oluşturulmaz
2. `basePath` = `$product->getStoragePath()` (ürün klasörü)
3. Mevcut `images` korunur, sadece yeni yüklenenler eklenir
4. Kapak değişirse `$images[0]` güncellenir
5. PDF değişirse yeni dosya yüklenir; yoksa `pdf_path` mevcut değerle kalır

---

## Route ve View Zinciri

```
GET /urunler/{product:uuid}/duzenle
  → ProductController::edit(Product $product)
  → view('admin.product-edit', ['product' => $product])
  → <x-product-create-form :productId="$product->uuid" />
```

---

## Güncelleme Route (Patch/Put)

- `PUT/PATCH /urunler/{product:uuid}` → `product.update`
- Güncelleme aslında Livewire form üzerinden yapılır; controller sadece redirect eder
- Silme: `DELETE /urunler/{product:uuid}` → `ProductController::destroy()`

---

## İlgili Dokümanlar

- [urunekle.md](urunekle.md) – Form alanları ve validasyon
- [urunlistesi.md](urunlistesi.md) – Ürün listesi ve silme
