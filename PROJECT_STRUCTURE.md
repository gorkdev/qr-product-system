# QR Product System - Proje Yapısı

QR kod ile paylaşılabilen ürün yönetim sistemi. Ziyaretçiler yönlendirme sayfasında 5 saniyelik geri sayım görür, ziyaret IP tabanlı konum ile kaydedilir.

## Kullanılan Teknolojiler

| Katman | Teknoloji | Açıklama |
|--------|-----------|----------|
| **Backend** | Laravel 12 | PHP framework |
| **Reaktif UI** | Livewire 4 | SPA benzeri Blade bileşenleri |
| **Frontend** | Blade, Vite, Tailwind CSS 4 | Template engine, build tool, CSS |
| **QR Kod** | endroid/qr-code | PNG QR üretimi |
| **Görsel işleme** | intervention/image-laravel | Thumbnail, resize |
| **İkonlar** | blade-ui-kit/blade-heroicons | SVG ikonlar |

Detaylı teknoloji açıklamaları için: [docs/teknolojiler.md](docs/teknolojiler.md)

---

## Proje Yapısı (Dosya/Klasör)

```
qr-product-system/
├── app/
│   ├── Http/Controllers/      # ProductController, SettingController, DashboardController
│   ├── Livewire/              # ProductList, VisitList
│   ├── Models/                # Product, ProductVisit, Setting, User
│   ├── Console/Commands/      # GenerateProductThumbnails
│   └── Providers/
├── resources/
│   ├── views/
│   │   ├── admin/             # Dashboard, ürün listesi, düzenleme, ayarlar, ziyaretler
│   │   ├── product/           # landing, qr-only, process, content partial
│   │   ├── layouts/           # admin.blade.php, public.blade.php
│   │   ├── livewire/          # product-list, visit-list
│   │   └── components/       # product-create-form, custom-select
│   ├── css/
│   └── js/
├── database/
│   └── migrations/            # products, product_visits, settings, vb.
├── routes/
│   └── web.php
├── public/
│   └── css/admin.css          # Admin panel stilleri
└── docs/                      # Detaylı dokümantasyon
```

---

## Yapılan İşlemler ve Detay Dokümanları

### Ürün Yönetimi

| İşlem | Açıklama | Detay |
|-------|----------|-------|
| **Ürün ekleme** | Yeni ürün oluşturma (ad, açıklama, görsel, video, PDF) | [docs/urunekle.md](docs/urunekle.md) |
| **Ürün düzenleme** | Mevcut ürün güncelleme | [docs/urunduzenle.md](docs/urunduzenle.md) |
| **Ürün listesi** | Arama, sıralama, silme | [docs/urunlistesi.md](docs/urunlistesi.md) |

### Ziyaretçi Akışı

| İşlem | Açıklama | Detay |
|-------|----------|-------|
| **Ürün gate** | `urun-bilgisi/{share_token}` tek giriş noktası | [docs/ziyaretci-akisi.md](docs/ziyaretci-akisi.md) |
| **Ziyaret kaydı** | IP, konum, cihaz, tarayıcı kaydı | [docs/ziyaret-takibi.md](docs/ziyaret-takibi.md) |
| **QR kod** | endroid ile QR üretimi, access_mode etkisi | [docs/qr-kod.md](docs/qr-kod.md) |

### Yönetim Paneli

| İşlem | Açıklama | Detay |
|-------|----------|-------|
| **Dashboard** | İstatistikler, en çok ziyaret edilen ürünler | [docs/dashboard.md](docs/dashboard.md) |
| **Ayarlar** | Erişim modu (link / qr_only) | [docs/ayarlar.md](docs/ayarlar.md) |
| **Ziyaret logları** | Filtreleme, arama, tarih aralığı | [docs/ziyaret-loglari.md](docs/ziyaret-loglari.md) |

### Altyapı

| Konu | Açıklama | Detay |
|------|----------|-------|
| **Veritabanı** | Tablolar, ilişkiler, migration sırası | [docs/veritabani.md](docs/veritabani.md) |
| **Kurulum** | Composer, .env, migrate, storage link | [docs/kurulum.md](docs/kurulum.md) |
| **Testler** | PHPUnit, DashboardTest, ProductTest | [docs/testler.md](docs/testler.md) |
| **Git** | Branches, commit conventions | [docs/git.md](docs/git.md) |

---

## Özet Akış Diyagramı

```
[Admin] Ürün ekle → Product oluştur → share_token üret
     ↓
[Admin] QR/ Link paylaş → urun-bilgisi/{share_token}

[Ziyaretçi] Link/QR ile gelir
     ↓
[Gate] access_mode kontrolü (link vs qr_only)
     ↓
[Landing] 5 sn geri sayım → saveVisit (IP, konum)
     ↓
[confirmEnter] Session set → İçerik göster
```

---

## Hızlı Başlangıç

1. Kurulum: [docs/kurulum.md](docs/kurulum.md)
2. Ürün ekleme: [docs/urunekle.md](docs/urunekle.md)
3. Ayarlar: [docs/ayarlar.md](docs/ayarlar.md)
