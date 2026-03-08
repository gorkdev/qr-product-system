@extends('layouts.public')

@section('title', 'QR Kod Gerekli')

@push('styles')
<style>
.public-qronly { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
.public-qronly-card { background: #fff; border-radius: 20px; padding: 2.5rem; max-width: 420px; width: 100%; text-align: center; box-shadow: 0 8px 32px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.04); }
.public-qronly-icon { width: 80px; height: 80px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.public-qronly-icon svg { width: 40px; height: 40px; color: #d97706; }
.public-qronly-title { margin: 0 0 0.75rem; font-size: 1.4rem; font-weight: 600; color: #171717; }
.public-qronly-desc { margin: 0; color: #525252; line-height: 1.65; font-size: 0.95rem; }
@media (min-width: 600px) { .public-qronly-card { padding: 3rem; } .public-qronly-title { font-size: 1.5rem; } }
</style>
@endpush

@section('content')
<div class="public-qronly">
    <div class="public-qronly-card">
        <div class="public-qronly-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
            </svg>
        </div>
        <h1 class="public-qronly-title">QR Kod Gerekli</h1>
        <p class="public-qronly-desc">Bu içeriğe yalnızca QR kod ile erişilebilir. Ürün QR kodunu tarayarak devam edebilirsiniz.</p>
    </div>
</div>
@endsection
