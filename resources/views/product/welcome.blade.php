@extends('layouts.public')

@section('title', $product->name)

@push('styles')
<style>
.public-welcome { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
.public-welcome-card { background: #fff; border-radius: 20px; padding: 2.5rem; max-width: 440px; width: 100%; text-align: center; box-shadow: 0 8px 32px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.04); }
.public-welcome-icon { width: 72px; height: 72px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.welcome-icon { width: 36px; height: 36px; color: #16a34a; }
.public-welcome-title { margin: 0 0 0.5rem; font-size: 1.6rem; font-weight: 600; color: #171717; letter-spacing: -0.02em; }
.public-welcome-desc { margin: 0 0 1.75rem; color: #525252; line-height: 1.65; font-size: 0.975rem; }
.public-welcome-btn { display: inline-block; padding: 0.875rem 1.75rem; background: linear-gradient(135deg, #171717 0%, #262626 100%); color: #fff; text-decoration: none; font-weight: 600; border-radius: 10px; transition: transform 0.15s, box-shadow 0.15s; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
.public-welcome-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); color: #fff; }
@media (min-width: 600px) { .public-welcome-card { padding: 3rem; } .public-welcome-title { font-size: 1.75rem; } }
</style>
@endpush

@section('content')
<div class="public-welcome">
    <div class="public-welcome-card">
        <div class="public-welcome-icon">
            <x-heroicon-o-check-circle class="welcome-icon" />
        </div>
        <h1 class="public-welcome-title">Hoş Geldiniz</h1>
        <p class="public-welcome-desc">{{ $product->name }} ürününün detaylı bilgilerine ulaşmak için aşağıdaki butona tıklayın. Verileriniz güvenle kaydedilecektir.</p>
        <a href="{{ route('product.gate', $product->share_token) }}?ref=qr" class="public-welcome-btn">Ürün Bilgisini Görüntüle</a>
    </div>
</div>
@endsection
