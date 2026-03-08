# Kurulum

Bu belge, projenin geliştirme ortamında kurulumunu adım adım açıklar.  
**Ana referans:** [PROJECT_STRUCTURE.md](../PROJECT_STRUCTURE.md)

---

## Gereksinimler

- PHP 8.2+
- Composer
- Node.js 18+ ve npm
- SQLite (varsayılan) veya MySQL / PostgreSQL

---

## Adım 1: Projeyi İndir

```bash
git clone https://github.com/gorkdev/qr-product-system.git
cd qr-product-system
```

Detay: [git.md](git.md)

---

## Adım 2: PHP Bağımlılıkları

```bash
composer install
```

---

## Adım 3: Ortam Değişkenleri

```bash
cp .env.example .env
php artisan key:generate
```

---

## Adım 4: Veritabanı

**SQLite (varsayılan):**

```bash
touch database/database.sqlite
php artisan migrate
```

**MySQL / PostgreSQL:**

`.env` dosyasında ayarlayın:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qr_product_system
DB_USERNAME=...
DB_PASSWORD=...
```

Sonra:

```bash
php artisan migrate
```

---

## Adım 5: Storage Link

```bash
php artisan storage:link
```

`public/storage` → `storage/app/public` symlink oluşturur (görsel, PDF, QR dosyaları için).

---

## Adım 6: Frontend

```bash
npm install
npm run build
```

**Geliştirme:** `npm run dev` (Vite HMR)

---

## Opsiyonel: Temiz Veritabanı

```bash
php artisan migrate:fresh
```

---

## Uygulamayı Çalıştırma

```bash
php artisan serve
```

Tarayıcıda: http://localhost:8000

### Composer dev script (tüm servisler)

```bash
composer run dev
```

Server, queue, pail (log), Vite paralel çalışır.

---

## Özet Komutlar

| Komut | Açıklama |
|-------|----------|
| `composer setup` | Tam kurulum (install, .env, key, migrate, npm, build) |
| `php artisan migrate` | Migration çalıştır |
| `php artisan storage:link` | Storage symlink |
| `php artisan test` | Testleri çalıştır |
