<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ürün') - QR Ürün Bilgisi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <style>
        .public-body { min-height: 100vh; background: #f5f5f5; padding: 1.5rem; }
        .public-product { max-width: 720px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .public-product__cover { width: 100%; aspect-ratio: 16/10; object-fit: cover; display: block; }
        .public-product__body { padding: 1.5rem 1.75rem; }
        .public-product__title { margin: 0 0 1rem; font-size: 1.5rem; font-weight: 600; color: #171717; }
        .public-product__desc { color: #525252; line-height: 1.7; white-space: pre-wrap; }
        .public-product__section { margin-top: 1.5rem; }
        .public-product__section-title { font-size: 0.85rem; font-weight: 600; color: #737373; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; }
        .public-product__gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 0.75rem; }
        .public-product__gallery img { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 8px; }
        .public-product__videos { display: flex; flex-wrap: wrap; gap: 0.75rem; }
        .public-product__video { flex: 1 1 280px; aspect-ratio: 16/9; border-radius: 8px; }
        .public-product__pdf { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1rem; background: #f5f5f5; border-radius: 8px; color: #262626; text-decoration: none; font-weight: 500; transition: background 0.15s; }
        .public-product__pdf:hover { background: #e5e5e5; }
        @media (min-width: 600px) { .public-body { padding: 2rem; } .public-product__body { padding: 2rem 2.25rem; } .public-product__title { font-size: 1.75rem; } }
    </style>
</head>
<body class="public-body">
    @yield('content')
</body>
</html>
