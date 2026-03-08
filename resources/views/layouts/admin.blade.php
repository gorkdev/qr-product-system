<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - Ürün Yönetimi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @livewireStyles
</head>
<body class="admin-body">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <x-heroicon-o-squares-2x2 class="sidebar-logo" />
            <span class="sidebar-title">QR Ürün</span>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" wire:navigate
               class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <x-heroicon-o-home class="nav-icon" />
                <span class="nav-label">Dashboard</span>
            </a>
            <a href="{{ route('product.index') }}" wire:navigate
               class="nav-item {{ request()->routeIs('product.index') || request()->routeIs('product.edit') ? 'active' : '' }}">
                <x-heroicon-o-shopping-bag class="nav-icon" />
                <span class="nav-label">Ürünler</span>
            </a>
            <a href="{{ route('product.create') }}" wire:navigate
               class="nav-item {{ request()->routeIs('product.create') ? 'active' : '' }}">
                <x-heroicon-o-plus-circle class="nav-icon" />
                <span class="nav-label">Yeni Ürün</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar">A</div>
                <span class="user-name">Admin</span>
            </div>
        </div>

    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

    <main class="admin-main">
        <header class="admin-header">
            <button type="button" class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Menü">
                <x-heroicon-o-bars-3 class="menu-icon" />
            </button>
            <h1 class="page-title">@yield('page-title', 'Yönetim')</h1>
        </header>

        <div class="admin-content">
            @yield('content')
        </div>
    </main>

    @livewireScripts
    @stack('scripts')
    <script>
        (function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const mobileBtn = document.getElementById('mobileMenuBtn');

            if (mobileBtn) mobileBtn.addEventListener('click', function() {
                sidebar.classList.add('open');
                overlay.classList.add('visible');
                document.body.classList.add('sidebar-open');
            });
            if (overlay) overlay.addEventListener('click', function() {
                sidebar.classList.remove('open');
                overlay.classList.remove('visible');
                document.body.classList.remove('sidebar-open');
            });
        })();
    </script>
</body>
</html>
