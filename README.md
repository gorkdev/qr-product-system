# QR Product System

A Laravel-based web application for managing products and sharing them via QR codes. Visitors are shown a redirect page with a countdown before accessing product content. The system tracks visits with IP-based geolocation.

## What It Does

- **Product Management**: Create, edit, and delete products with name, description, images, YouTube videos, and PDF documents
- **QR Code Generation**: Each product gets a unique shareable link and QR code
- **Visit Tracking**: Logs every visit with IP, location (city, country, region), device type, browser, and ISP
- **Access Control**: Two modes—allow all links or restrict access to QR code scans only
- **Redirect Flow**: Visitors see a brief countdown (5 seconds) while visit data is saved, then view the product content
- **Dashboard**: Overview of product count, total visits, visits today/week/month, and top products

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+ and npm
- SQLite (default) or MySQL/PostgreSQL

## Clone the Project

```bash
git clone https://github.com/gorkdev/qr-product-system.git
cd qr-product-system
```

## Installation (Step by Step)

### 1. Install PHP Dependencies

```bash
composer install
```

### 2. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure the Database

The project uses SQLite by default. Create the database file:

```bash
touch database/database.sqlite
```

For MySQL or PostgreSQL, update `.env` accordingly:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qr_product_system
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Run Migrations

```bash
php artisan migrate
```

This creates all required tables: products, product_visits, settings, cache, sessions, jobs.

### 5. Create Storage Link

```bash
php artisan storage:link
```

### 6. Install Frontend Dependencies

```bash
npm install
npm run build
```

### 7. (Optional) Fresh Database

To start with a clean database (no products, visits, or settings):

```bash
php artisan migrate:fresh
```

## Running the Application

### Development Server

```bash
php artisan serve
```

Then open [http://localhost:8000](http://localhost:8000) in your browser.

### Admin Panel

- **Dashboard**: `http://localhost:8000/`
- **Products**: `http://localhost:8000/urunler`
- **Add Product**: `http://localhost:8000/urunler/yeni`
- **Visit Logs**: `http://localhost:8000/ziyaretler`
- **Settings**: `http://localhost:8000/ayarlar`

### Public Product Links

When a product is created, it gets a share token. The public URL format is:

```
http://localhost:8000/urun-bilgisi/{share_token}
```

Visitors see a redirect page with a 5-second countdown, then the product content (images, description, videos, PDF).

## IP Geolocation

Visit location is fetched from [ip-api.com](http://ip-api.com/json/). No API key is required for basic usage. Data saved: country, region, city, timezone, ISP, latitude, longitude.

## Running Tests

```bash
php artisan test
```

## Tech Stack

- **Backend**: Laravel 12, Livewire
- **Frontend**: Blade, Tailwind CSS (via Vite), Alpine.js
- **QR Codes**: endroid/qr-code
- **Images**: intervention/image-laravel

## License

MIT
