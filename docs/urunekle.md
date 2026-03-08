# Ürün Ekleme

Bu belge, QR Product System’de yeni ürün ekleme işlemini adım adım açıklar.  
**Ana referans:** [PROJECT_STRUCTURE.md](../PROJECT_STRUCTURE.md)

---

## Genel Bakış

- **URL:** `/urunler/yeni` → `product.create` route
- **Controller:** `ProductController::create()` → `admin.product-create` view
- **Form bileşeni:** Livewire anonymous component `⚡product-create-form`  
  - Dosya: `resources/views/components/⚡product-create-form.blade.php`

---

## Form Alanları

| Alan | Zorunlu | Validasyon | Açıklama |
|------|---------|------------|----------|
| Ürün Adı | Evet | min:3, max:255 | Ürün başlığı |
| Açıklama | Evet | min:10 | Ürün detay metni |
| Kapak Görseli | Evet (yeni) | image, jpeg/png/jpg/webp, max:2MB | Ana görsel |
| Ek Görseller | Hayır | Aynı kurallar | Çoklu görsel |
| YouTube Videoları | Hayır | Geçerli youtube.com / youtu.be URL | Birden fazla video |
| PDF | Hayır | pdf, max:10MB | Tek PDF dosyası |

---

## Kaydetme Akışı (save metodu)

1. **Validasyon**
   - `name`, `description` zorunlu
   - Yeni ürün için `main_image` zorunlu
   - YouTube URL’leri regex ile kontrol: `/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+/`

2. **Ürün oluşturma**
   - `Product::create()` – `name`, `description`, boş `images`, `videos`
   - `Product` model `booted()` ile otomatik `uuid` ve `share_token` atar

3. **Dosya depolama**
   - Base path: `products/{uuid}/`
   - Görseller: `products/{uuid}/images/` ve `products/{uuid}/images/thumbs/`
   - PDF: `products/{uuid}/pdf/`

4. **Görsel işleme (Intervention Image)**
   - Ana görsel: `scaleDown(width: 1920)`, quality 90
   - Thumbnail: `cover(128, 128)`, quality 85
   - Hata durumunda ham dosya kaydedilir

5. **Güncelleme**
   - `images` (JSON array), `videos` (JSON array), `pdf_path` güncellenir

6. **Sonuç**
   - Başarı mesajı
   - `createdProductLink` – `access_mode`’a göre link (`?ref=qr` eklenebilir)
   - QR kod path: `Product::getQrCodePath()`

---

## Dosya Yükleme Akışı (Livewire)

- Form submit öncesi `uploadThenSave()` (Alpine.js) ile:
  1. `main_image` dosyası
  2. `additional_images.*` dosyaları
  3. `pdf` dosyası
- `$wire.$upload()` ile Livewire’a gönderilir
- Ardından `$wire.save()` çağrılır

---

## İlgili Dosyalar

| Dosya | Açıklama |
|-------|----------|
| `app/Models/Product.php` | Model, storage path, QR üretimi |
| `resources/views/components/⚡product-create-form.blade.php` | Form bileşeni |
| `resources/views/admin/product-create.blade.php` | Sayfa layout |
| `config/filesystems.php` | Storage disk yapılandırması |

---

## Route ve View Zinciri

```
GET /urunler/yeni
  → ProductController::create()
  → view('admin.product-create')
  → <x-product-create-form /> (mount without productId)
```
