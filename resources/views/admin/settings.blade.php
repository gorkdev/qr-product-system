@extends('layouts.admin')

@section('title', 'Ayarlar')
@section('page-title', 'Ayarlar')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('setting.update') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Yönlendirme sitesi erişimi</label>
                    <div class="settings-options">
                        <label class="settings-option">
                            <input type="radio" name="access_mode" value="link" {{ ($accessMode ?? 'link') === 'link' ? 'checked' : '' }}>
                            <span class="settings-option-label">Her linke gidişinde açılsın</span>
                            <span class="settings-option-desc">Link veya QR ile herkes ürün sayfasına gidebilir</span>
                        </label>
                        <label class="settings-option">
                            <input type="radio" name="access_mode" value="qr_only" {{ ($accessMode ?? '') === 'qr_only' ? 'checked' : '' }}>
                            <span class="settings-option-label">Sadece QR kod ile açılsın</span>
                            <span class="settings-option-desc">Yalnızca QR kod taranarak erişilebilir</span>
                        </label>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
@endsection
