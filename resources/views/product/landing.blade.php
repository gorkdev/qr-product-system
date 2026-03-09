@extends('layouts.public')

@section('title', $product->name)

@section('body_class', ($showRedirect ?? false) ? 'landing-page' : '')

@push('styles')
<style>
.landing-loader, .landing-redirect { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; overflow-x: hidden; box-sizing: border-box; background: #faf9f7; }
body.landing-page { overflow: hidden; height: 100vh; margin: 0; padding: 0; }
.landing-loader.visible, .landing-redirect.visible { position: fixed; inset: 0; z-index: 10; }
.landing-card { background: #fff; border-radius: 16px; padding: 2.5rem; max-width: 420px; width: 100%; text-align: center; box-shadow: 0 10px 40px -15px rgba(0,0,0,0.1); border: 1px solid #e7e5e4; }
.landing-spinner { width: 44px; height: 44px; margin: 0 auto 1.5rem; border: 3px solid #e7e5e4; border-top-color: #b45309; border-radius: 50%; animation: landing-spin 0.7s linear infinite; }
@keyframes landing-spin { to { transform: rotate(360deg); } }
.landing-msg { margin: 0; font-size: 1rem; color: #57534e; font-weight: 500; }
.landing-timer { margin-top: 0.625rem; font-size: 0.9rem; color: #78716c; min-height: 1.25em; }
.landing-content-wrap { display: none; }
.landing-content-wrap.visible { display: block; }
.landing-redirect.visible { display: flex; }
.landing-redirect { display: none; }
.landing-loader.visible { display: flex; }
.landing-loader { display: none; }
.landing-error { display: none; margin-top: 1.25rem; padding: 1rem 1.25rem; background: #fef2f2; border-radius: 10px; font-size: 0.9rem; color: #991b1b; border: 1px solid #fecaca; }
.landing-error.visible { display: block; }
.landing-btn { margin-top: 1rem; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #b45309 0%, #92400e 100%); color: #fff; border: none; border-radius: 10px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: transform 0.15s, box-shadow 0.15s; box-shadow: 0 4px 14px -4px rgba(180, 83, 9, 0.4); }
.landing-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px -4px rgba(180, 83, 9, 0.5); }
@media (min-width: 600px) { .landing-card { padding: 3rem; } }
</style>
@endpush

@section('content')
<div id="landingApp">
    {{-- Loader - ilk açılışta --}}
    <div class="landing-loader" id="loaderSection">
        <div class="landing-card">
            <div class="landing-spinner"></div>
            <p class="landing-msg">Yükleniyor...</p>
        </div>
    </div>

    {{-- Yönlendirme - ayarlara göre gösterilir --}}
    <div class="landing-redirect" id="redirectSection">
        <div class="landing-card">
            <div class="landing-spinner" id="redirectSpinner"></div>
            <p class="landing-msg">Yönlendiriliyorsunuz, lütfen bekleyin.</p>
            <p class="landing-msg landing-timer" id="timerText">5 sn</p>
            <div class="landing-error" id="errorBlock">
                <p id="errorMsg"></p>
                <button type="button" class="landing-btn" id="retryBtn">Devam Et</button>
            </div>
        </div>
    </div>

    {{-- İçerik --}}
    <div class="landing-content-wrap" id="contentSection">
        @include('product.partials.content', ['product' => $product])
    </div>
</div>

<script>
(function() {
    const showRedirect = @json($showRedirect);
    const showContent = @json($showContent);

    const loaderSection = document.getElementById('loaderSection');
    const redirectSection = document.getElementById('redirectSection');
    const contentSection = document.getElementById('contentSection');
    const timerText = document.getElementById('timerText');
    const errorBlock = document.getElementById('errorBlock');
    const errorMsg = document.getElementById('errorMsg');
    const retryBtn = document.getElementById('retryBtn');

    const saveUrl = '{{ route('product.saveVisit', $product->share_token) }}';
    const anonymousUrl = '{{ route('product.saveVisitAnonymous', $product->share_token) }}';
    const confirmUrl = '{{ route('product.confirmEnter', $product->share_token) }}';
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const MIN_WAIT_MS = 5000;

    if (showContent) {
        contentSection.classList.add('visible');
        return;
    }

    document.body.classList.add('landing-page');
    loaderSection.classList.add('visible');
    setTimeout(function() {
        loaderSection.classList.remove('visible');
        redirectSection.classList.add('visible');
    }, 200);

    let saveComplete = false;
    let saveError = null;
    let redirecting = false;
    const startTime = Date.now();

    function showContentAndHideRest() {
        document.body.classList.remove('landing-page');
        loaderSection.classList.remove('visible');
        redirectSection.classList.remove('visible');
        contentSection.classList.add('visible');
    }

    function updateTimer() {
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        const remaining = Math.max(0, Math.ceil((MIN_WAIT_MS / 1000) - elapsed));
        timerText.textContent = remaining + ' sn';
    }

    function showError(err) {
        errorMsg.textContent = err || 'Bir hata oluştu';
        errorBlock.classList.add('visible');
    }

    function doRedirect() {
        if (redirecting) return;
        redirecting = true;
        fetch(confirmUrl, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(() => showContentAndHideRest())
        .catch(() => showContentAndHideRest());
    }

    function saveAnonymousAndContinue(err) {
        retryBtn.disabled = true;
        retryBtn.textContent = 'Bekleyin...';
        fetch(anonymousUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ error: err || 'Bilinmeyen hata' }),
        })
        .then(() => doRedirect())
        .catch(() => showContentAndHideRest());
    }

    retryBtn.addEventListener('click', () => saveAnonymousAndContinue(saveError));

    fetch(saveUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        saveComplete = true;
        if (!data.success) showError(data.error || 'Kayıt başarısız');
    })
    .catch(err => {
        saveComplete = true;
        saveError = err.message || 'Ağ hatası';
        showError(saveError);
    });

    const checkInterval = setInterval(function() {
        updateTimer();
        if (!saveComplete || saveError) return;
        if (Date.now() - startTime >= MIN_WAIT_MS) {
            clearInterval(checkInterval);
            doRedirect();
        }
    }, 100);
})();
</script>
@endsection
