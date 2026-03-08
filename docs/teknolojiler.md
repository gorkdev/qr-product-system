# Kullanılan Teknolojiler

Bu belge, QR Product System projesinde kullanılan tüm teknolojileri detaylıca açıklar.  
**Ana referans:** [PROJECT_STRUCTURE.md](../PROJECT_STRUCTURE.md)

---

## Backend

### Laravel 12

- **Ne işe yarar:** PHP web framework, routing, ORM, validation, session
- **Projede kullanım alanları:**
  - `routes/web.php` – Tüm web rotaları
  - `app/Http/Controllers/` – HTTP istekleri işlenir
  - `app/Models/` – Eloquent modelleri
  - Migration, cache, config

### Livewire 4

- **Ne işe yarar:** Blade tabanlı reaktif bileşenler, sayfa yenilemeden CRUD
- **Projede kullanım alanları:**
  - `app/Livewire/ProductList.php` – Ürün listesi, arama, sıralama, silme
  - `app/Livewire/VisitList.php` – Ziyaret logları, filtreleme
  - `resources/views/components/⚡product-create-form.blade.php` – Ürün ekleme/düzenleme formu (anonymous Livewire component)
- **Özellikler:** `WithPagination`, `WithFileUploads`, wire:model, wire:click

---

## Frontend

### Blade

- **Ne işe yarar:** Laravel template engine
- **View klasör yapısı:**
  - `resources/views/admin/` – Dashboard, ürün CRUD, ayarlar, ziyaretler
  - `resources/views/product/` – Landing, qr-only, content partial
  - `resources/views/layouts/` – admin.blade.php, public.blade.php
  - `resources/views/livewire/` – Livewire bileşen şablonları

### Vite

- **Ne işe yarar:** Modern build tool, HMR ile geliştirme
- **Config:** `vite.config.js`, `package.json` scripts
- **Çıktı:** `public/build/` (production), dev modda Vite dev server

### Tailwind CSS 4

- **Ne işe yarar:** Utility-first CSS framework
- **Kullanım:** `@tailwindcss/vite` plugin, `resources/css/app.css`
- **Not:** Admin paneli ayrıca `public/css/admin.css` ile custom stiller kullanır

### Alpine.js

- **Ne işe yarar:** Hafif JavaScript framework (Livewire ile birlikte)
- **Kullanım:** `x-data`, `x-show`, `x-text`, `@click` vb. – özellikle form bileşenlerinde

---

## QR Kod ve Medya

### endroid/qr-code (^6.0)

- **Ne işe yarar:** QR kod PNG üretimi
- **Projede kullanım:** `App\Models\Product`
  - `generateQrCode()` – URL’e göre QR oluşturur
  - `getQrCodePath()` – Dosya yolunu döner, yoksa oluşturur
- **URL formatı:**  
  - `link` modu: `{base}/urun-bilgisi/{share_token}`  
  - `qr_only` modu: `{base}/urun-bilgisi/{share_token}?ref=qr`

### intervention/image-laravel (^1.5)

- **Ne işe yarar:** Görsel resize, thumbnail, format dönüşümü
- **Kullanım:** Product create form’da
  - Ana görsel: max 1920px genişlik, quality 90
  - Thumbnail: 128x128 cover, quality 85
- **Desteklenen formatlar:** JPEG, PNG, JPG, WEBP

### blade-ui-kit/blade-heroicons (^2.6)

- **Ne işe yarar:** Heroicons SVG bileşenleri
- **Kullanım:** `<x-heroicon-o-check-circle />`, `<x-heroicon-o-photo />` vb.

---

## Veritabanı

- **Varsayılan:** SQLite (`database/database.sqlite`)
- **Alternatifler:** MySQL, PostgreSQL – `.env` ile yapılandırılır
- **Migration sırası:** `database/migrations/` içindeki dosya adlarına göre
- Detay: [veritabani.md](veritabani.md)

---

## Test ve Geliştirme

| Araç | Kullanım |
|------|----------|
| PHPUnit 11 | `php artisan test --testdox` |
| Laravel Pail | Log takibi `php artisan pail` |
| Composer dev script | `composer run dev` – server, queue, logs, vite paralel |

---

## Bağımlılık Versiyonları (composer.json)

```json
{
  "require": {
    "php": "^8.2",
    "blade-ui-kit/blade-heroicons": "^2.6",
    "endroid/qr-code": "^6.0",
    "intervention/image-laravel": "^1.5",
    "laravel/framework": "^12.0",
    "livewire/livewire": "^4.2"
  }
}
```
