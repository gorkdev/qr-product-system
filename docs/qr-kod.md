# QR Kod Sistemi

Bu belge, QR kod üretimi ve access_mode etkisini açıklar.  
**Ana referans:** [PROJECT_STRUCTURE.md](../PROJECT_STRUCTURE.md)

---

## Kütüphane

- **Paket:** endroid/qr-code ^6.0
- **Kullanım:** `Product` modelinde PNG üretimi

---

## QR URL Formatı

Base URL: `url(route('product.gate', $product->share_token))`

| access_mode | URL |
|-------------|-----|
| `link` | `https://example.com/urun-bilgisi/{share_token}` |
| `qr_only` | `https://example.com/urun-bilgisi/{share_token}?ref=qr` |

`qr_only` modunda sadece `?ref=qr` parametreli URL geçerli; QR kod bu URL ile üretilir.

---

## Üretim: getQrCodePath()

**Dosya:** `app/Models/Product.php`

```php
public function getQrCodePath(): string
{
    $path = $this->getStoragePath() . 'qr.png';
    $accessMode = Setting::get('access_mode', 'link');
    $this->generateQrCode($path, $accessMode);
    return Storage::url($path);
}
```

1. Path: `products/{uuid}/qr.png`
2. `Setting::get('access_mode')` ile mode alınır
3. `generateQrCode()` ile dosya oluşturulur
4. Storage URL döner

---

## generateQrCode() Detayı

```php
$baseUrl = url(route('product.gate', $this->share_token));
$url = $accessMode === 'qr_only' ? $baseUrl . '?ref=qr' : $baseUrl;
$qrCode = new QrCode($url);
$writer = new PngWriter();
$result = $writer->write($qrCode);
Storage::disk('public')->put($path, $result->getString());
```

- Klasör yoksa `Storage::disk('public')->makeDirectory(dirname($path))` ile oluşturulur
- QR her çağrıda yeniden üretilir (access_mode değişince URL güncellenir)

---

## Kullanım Yerleri

1. **Ürün listesi (ProductList):** Her ürün için `$product->getQrCodePath()` → QR görseli
2. **Ürün formu (product-create-form):** Kayıt sonrası QR kutusunda gösterilir ve indirilebilir
3. **Ziyaretçi tarafı:** QR tarandığında `?ref=qr` ile gate’e gider

---

## access_mode Kontrolü (Gate)

`ProductController::isQrAccessAllowed()`:

- `link` → her zaman true
- `qr_only` → sadece `$request->query('ref') === 'qr'` ise true

---

## Dosya Konumu

- **Disk:** `public` (storage/app/public)
- **Path:** `products/{uuid}/qr.png`
- **Symlink:** `php artisan storage:link` ile `public/storage` → `storage/app/public`

---

## İlgili Dokümanlar

- [ayarlar.md](ayarlar.md) – access_mode ayarı
- [ziyaretci-akisi.md](ziyaretci-akisi.md) – Gate ve ref=qr kullanımı
