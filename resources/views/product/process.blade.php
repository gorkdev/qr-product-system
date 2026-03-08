@extends('layouts.public')

@section('title', $product->name . ' - Yükleniyor')

@push('styles')
<style>
.public-process { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
.public-process-card { background: #fff; border-radius: 16px; padding: 2.5rem; max-width: 420px; width: 100%; text-align: center; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
.public-process-spinner { width: 48px; height: 48px; margin: 0 auto 1.5rem; border: 4px solid #e5e5e5; border-top-color: #171717; border-radius: 50%; animation: process-spin 0.8s linear infinite; }
@keyframes process-spin { to { transform: rotate(360deg); } }
.public-process-title { margin: 0 0 0.5rem; font-size: 1.35rem; font-weight: 600; color: #171717; }
.public-process-desc { margin: 0 0 0.75rem; color: #525252; font-size: 0.95rem; line-height: 1.5; }
.public-process-timer { margin: 1rem 0 0; font-size: 0.9rem; color: #737373; }
.public-process-error .public-process-error-icon { width: 64px; height: 64px; margin: 0 auto 1.25rem; color: #dc2626; }
.public-process-error-msg { margin: 1rem 0 1.5rem; padding: 1rem; background: #fef2f2; border-radius: 8px; font-size: 0.9rem; color: #991b1b; text-align: left; word-break: break-word; }
.public-process-btn { display: inline-block; padding: 0.75rem 1.5rem; background: #171717; color: #fff; text-decoration: none; font-weight: 600; border-radius: 8px; transition: background 0.15s; border: none; cursor: pointer; font-size: 1rem; font-family: inherit; }
.public-process-btn:hover:not(:disabled) { background: #404040; color: #fff; }
.public-process-btn:disabled { opacity: 0.7; cursor: not-allowed; }
@media (min-width: 600px) { .public-process-card { padding: 3rem; } .public-process-title { font-size: 1.5rem; } }
</style>
@endpush

@section('content')
    <div class="public-process" id="processContainer">
        <div class="public-process-card" id="loaderCard">
            <div class="public-process-spinner"></div>
            <h2 class="public-process-title">Yönlendiriliyorsunuz, lütfen bekleyin...</h2>
            <p class="public-process-timer" id="timerText"></p>
        </div>

        <div class="public-process-card public-process-error" id="errorCard" style="display: none;">
            <div class="public-process-error-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="48" height="48">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h2 class="public-process-title">Yönlendirme sırasında hata oluştu</h2>
            <p class="public-process-desc">Yönlendirmeye devam etmek için aşağıdaki butona tıklayın. Ziyaret anonim olarak kaydedilecektir.</p>
            <p class="public-process-error-msg" id="errorMessage"></p>
            <button type="button" class="public-process-btn" id="manualRedirectBtn" style="display: none;">
                Yönlendirmeye Devam Et
            </button>
        </div>
    </div>

    <script>
    (function() {
        const MIN_WAIT_MS = 5000;
        const container = document.getElementById('processContainer');
        const loaderCard = document.getElementById('loaderCard');
        const errorCard = document.getElementById('errorCard');
        const processStatus = document.getElementById('processStatus');
        const timerText = document.getElementById('timerText');
        const errorMessage = document.getElementById('errorMessage');
        const manualRedirectBtn = document.getElementById('manualRedirectBtn');

        const saveUrl = '{{ route('product.saveVisit', $product->share_token) }}';
        const anonymousUrl = '{{ route('product.saveVisitAnonymous', $product->share_token) }}';
        const confirmUrl = '{{ url(route('product.confirmEnter', $product->share_token)) }}';

        const token = document.querySelector('meta[name="csrf-token"]')?.content;

        let saveComplete = false;
        let saveError = null;
        let redirecting = false;
        const startTime = Date.now();

        function updateTimer() {
            const elapsed = Math.floor((Date.now() - startTime) / 1000);
            const remaining = Math.max(0, Math.ceil((MIN_WAIT_MS / 1000) - elapsed));
            if (remaining > 0) {
                timerText.textContent = 'Yönlendirme: ' + remaining + ' sn';
            } else {
                timerText.textContent = 'Yönlendiriliyor...';
            }
        }

        function showError(err) {
            loaderCard.style.display = 'none';
            errorCard.style.display = 'block';
            errorMessage.textContent = err || 'Bilinmeyen hata';
            manualRedirectBtn.style.display = 'inline-block';
        }

        function saveAnonymousAndRedirect(err) {
            manualRedirectBtn.disabled = true;
            manualRedirectBtn.textContent = 'Kaydediliyor...';
            fetch(anonymousUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ error: err || 'Bilinmeyen hata' }),
            }).finally(function() {
                window.location.href = confirmUrl;
            });
        }

        function redirect() {
            if (redirecting) return;
            redirecting = true;
            window.location.replace(confirmUrl);
        }

        fetch(saveUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            saveComplete = true;
            if (data.success) {
                processStatus.textContent = 'Yönlendiriliyorsunuz, lütfen bekleyin...';
            } else {
                showError(data.error || 'Yönlendirme sırasında hata oluştu');
            }
        })
        .catch(err => {
            saveComplete = true;
            saveError = err.message || 'Yönlendirme sırasında ağ hatası oluştu';
            showError(saveError);
        });

        manualRedirectBtn.addEventListener('click', function() {
            saveAnonymousAndRedirect(saveError || errorMessage.textContent);
        });

        let timerInterval = setInterval(updateTimer, 200);
        let checkInterval = setInterval(function() {
            updateTimer();
            if (!saveComplete || saveError) return;
            const elapsed = Date.now() - startTime;
            if (elapsed >= MIN_WAIT_MS) {
                clearInterval(timerInterval);
                clearInterval(checkInterval);
                redirect();
            }
        }, 100);
    })();
    </script>
@endsection
