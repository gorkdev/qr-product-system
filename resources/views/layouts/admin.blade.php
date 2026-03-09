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
<body class="admin-body" @if(session('success')) data-flash-success="{{ e(session('success')) }}" @endif>
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
            <a href="{{ route('visit.index') }}" wire:navigate
               class="nav-item {{ request()->routeIs('visit.index') ? 'active' : '' }}">
                <x-heroicon-o-eye class="nav-icon" />
                <span class="nav-label">Ziyaret Logları</span>
            </a>
            <a href="{{ route('setting.index') }}"
               class="nav-item {{ request()->routeIs('setting.index') ? 'active' : '' }}">
                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="nav-label">Ayarlar</span>
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

    <div id="toast-container" class="toast-container" aria-live="polite" aria-label="Bildirimler"
         x-data="toastContainer()">
        <template x-for="(t, i) in toasts" :key="t.id">
            <div :class="['toast', 'toast--' + t.type]"
                 x-show="t.visible"
                 x-transition:enter="toast-enter"
                 x-transition:enter-start="toast-enter-start"
                 x-transition:enter-end="toast-enter-end"
                 x-transition:leave="toast-leave"
                 x-transition:leave-start="toast-leave-start"
                 x-transition:leave-end="toast-leave-end"
                 @transitionend="if ($event.propertyName === 'opacity') removeToast(t.id)">
                <span class="toast-dot toast-dot--success" x-show="t.type === 'success'" x-cloak></span>
                <span class="toast-dot toast-dot--error" x-show="t.type === 'error'" x-cloak></span>
                <span class="toast-dot toast-dot--waiting" x-show="t.type === 'waiting'"></span>
                <span class="toast-message" x-text="t.message"></span>
            </div>
        </template>
    </div>

    @livewireScripts
    @stack('scripts')
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('toast', {
            toasts: [],
            show(type, message) {
                const id = Date.now() + Math.random();
                this.toasts.push({ id, type: type || 'success', message: message || '', visible: true });
                const duration = type === 'waiting' ? 8000 : 4000;
                setTimeout(() => {
                    const t = this.toasts.find(x => x.id === id);
                    if (t) t.visible = false;
                }, duration);
            },
            removeToast(id) {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }
        });
        Alpine.data('toastContainer', () => ({
            get toasts() { return Alpine.store('toast').toasts },
            removeToast(id) { Alpine.store('toast').removeToast(id) }
        }));
        window.showToast = (type, message) => Alpine.store('toast').show(type, message);

        const flashMsg = document.body.dataset.flashSuccess;
        if (flashMsg) Alpine.store('toast').show('success', flashMsg);

        if (window.__customSelectRegistered) return;
        window.__customSelectRegistered = true;
        Alpine.data('customSelect', (config = {}) => ({
            open: false,
            value: '',
            options: config.options || {},
            placeholder: config.placeholder || 'Seçiniz',
            init() {
                this.$nextTick(() => {
                    this.value = this.$refs.selectEl?.value ?? '';
                });
            },
            getLabel() {
                return this.options[this.value] ?? this.options[this.$refs.selectEl?.value] ?? this.placeholder;
            },
            choose(val) {
                this.value = val;
                if (this.$refs.selectEl) {
                    this.$refs.selectEl.value = val;
                    this.$refs.selectEl.dispatchEvent(new Event('input', { bubbles: true }));
                    this.$refs.selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                }
                this.open = false;
            }
        }));
    });

    document.addEventListener('livewire:init', () => {
        Livewire.on('toast', (e) => {
            const d = e?.detail ?? e;
            if (window.showToast) window.showToast(d?.type || 'success', d?.message || '');
        });
    });
    </script>
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
