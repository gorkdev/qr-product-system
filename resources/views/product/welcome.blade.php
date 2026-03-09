@extends('layouts.public')

@section('title', $product->name)

@push('styles')
<style>
.public-welcome { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; background: #faf9f7; }
.public-welcome-card { background: #fff; border-radius: 20px; padding: 2.5rem; max-width: 440px; width: 100%; text-align: center; box-shadow: 0 10px 40px -15px rgba(0,0,0,0.1); border: 1px solid #e7e5e4; }
.public-welcome-icon { width: 80px; height: 80px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.welcome-icon { width: 40px; height: 40px; color: #b45309; }
.public-welcome-title { margin: 0 0 0.5rem; font-size: 1.6rem; font-weight: 700; color: #1c1917; letter-spacing: -0.02em; }
.public-welcome-desc { margin: 0 0 1.75rem; color: #57534e; line-height: 1.65; font-size: 1rem; }
.public-welcome-btn { display: inline-block; padding: 0.875rem 1.75rem; background: linear-gradient(135deg, #b45309 0%, #92400e 100%); color: #fff !important; text-decoration: none; font-weight: 600; border-radius: 10px; transition: transform 0.15s, box-shadow 0.15s; box-shadow: 0 4px 14px -4px rgba(180, 83, 9, 0.4); }
.public-welcome-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px -4px rgba(180, 83, 9, 0.5); color: #fff !important; }
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
