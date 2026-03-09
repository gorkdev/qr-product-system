<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ürün') - QR Ürün Bilgisi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/product.css') }}">
    @stack('styles')
    <style>
        .public-body { min-height: 100vh; background: #f5f4f2; padding: 0.5rem; }
        @media (min-width: 640px) { .public-body { padding: 2rem; } }
    </style>
</head>
<body class="public-body @yield('body_class')">
    @yield('content')
</body>
</html>
