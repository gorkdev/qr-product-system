@extends('layouts.admin')

@section('title', 'QR Ayarları')
@section('page-title', 'QR Ayarları')

@section('content')
    <div class="card">
        <div class="card-body" style="display:flex; flex-wrap:wrap; gap:2rem;">
            <div style="flex:1 1 260px; min-width:0;">
                @if(session('success'))
                    <div class="alert alert-success">
                        <x-heroicon-o-check-circle class="alert-icon" />
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('setting.update') }}" id="qrSettingsForm">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">QR Kod Renkleri</label>
                        <p class="text-secondary" style="font-size:0.85rem; margin-top:0; margin-bottom:0.75rem;">
                            Tüm ürünler için geçerli olacak ana QR kod renklerini seçin.
                        </p>
                        <div style="display:flex; flex-wrap:wrap; gap:1rem;">
                            <div>
                                <label class="text-secondary" style="font-size:0.85rem; display:block; margin-bottom:0.25rem;">Ön plan</label>
                                <input type="color" name="qr_foreground" id="qrForeground"
                                       value="{{ old('qr_foreground', $qr['foreground'] ?? '#111827') }}">
                            </div>
                            <div>
                                <label class="text-secondary" style="font-size:0.85rem; display:block; margin-bottom:0.25rem;">Arka plan</label>
                                <input type="color" name="qr_background" id="qrBackground"
                                       value="{{ old('qr_background', $qr['background'] ?? '#ffffff') }}">
                            </div>
                        </div>
                        @error('qr_foreground')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        @error('qr_background')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Etiket (QR altındaki metin)</label>
                        <input type="text"
                               name="label_text"
                               id="labelText"
                               class="form-input"
                               placeholder="Örn: Akçan Grup"
                               value="{{ old('label_text', $qr['label_text'] ?? '') }}">
                        <div style="display:flex; flex-wrap:wrap; gap:1rem; margin-top:0.75rem;">
                            <div style="min-width:130px;">
                                <label class="text-secondary" style="font-size:0.85rem; display:block; margin-bottom:0.25rem;">Etiket konumu</label>
                                @php $position = old('label_position', $qr['label_position'] ?? 'bottom'); @endphp
                                <x-custom-select
                                    name="label_position"
                                    id="labelPosition"
                                    :options="[
                                        'bottom' => 'QR altında',
                                        'top' => 'QR üstünde'
                                    ]"
                                    placeholder="Konum"
                                />
                            </div>
                            <div style="min-width:130px;">
                                <label class="text-secondary" style="font-size:0.85rem; display:block; margin-bottom:0.25rem;">Hizalama</label>
                                @php $align = old('label_align', $qr['label_align'] ?? 'center'); @endphp
                                <x-custom-select
                                    name="label_align"
                                    id="labelAlign"
                                    :options="['left' => 'Sola', 'center' => 'Ortaya', 'right' => 'Sağa']"
                                    placeholder="Hizalama"
                                />
                            </div>
                            <div style="min-width:130px;">
                                <label class="text-secondary" style="font-size:0.85rem; display:block; margin-bottom:0.25rem;">Metin rengi</label>
                                <input type="color" name="label_color" id="labelColor"
                                       value="{{ old('label_color', $qr['label_color'] ?? '#111827') }}">
                            </div>
                        </div>
                        <div style="display:flex; flex-wrap:wrap; gap:1rem; margin-top:0.75rem;">
                            <div style="min-width:150px;">
                                <label class="text-secondary" style="font-size:0.85rem; display:block; margin-bottom:0.25rem;">Yazı tipi</label>
                                @php $font = old('label_font', $qr['label_font'] ?? 'dm_sans'); @endphp
                                <x-custom-select
                                    name="label_font"
                                    id="labelFont"
                                    :options="[
                                        'dm_sans' => 'DM Sans',
                                        'open_sans' => 'Open Sans',
                                        'mono' => 'Monospace'
                                    ]"
                                    placeholder="Yazı tipi"
                                />
                            </div>
                            <div style="min-width:130px;">
                                <label class="text-secondary" style="font-size:0.85rem; display:block; margin-bottom:0.25rem;">Yazı boyutu</label>
                                @php $fontSize = (int) old('label_font_size', $qr['label_font_size'] ?? 16); @endphp
                                <x-custom-select
                                    name="label_font_size"
                                    id="labelFontSize"
                                    :options="[
                                        12 => '12 px',
                                        14 => '14 px',
                                        16 => '16 px',
                                        18 => '18 px',
                                        20 => '20 px',
                                        22 => '22 px',
                                        24 => '24 px',
                                        26 => '26 px',
                                        28 => '28 px'
                                    ]"
                                    placeholder="Boyut"
                                />
                            </div>
                            <div style="min-width:120px;">
                                <label class="text-secondary" style="font-size:0.85rem; display:block; margin-bottom:0.25rem;">Üst boşluk</label>
                                @php $mt = (int) old('label_margin_top', $qr['label_margin_top'] ?? 8); @endphp
                                <input type="number"
                                       name="label_margin_top"
                                       id="labelMarginTop"
                                       class="form-input"
                                       min="0"
                                       max="40"
                                       step="1"
                                       value="{{ $mt }}"
                                       style="max-width:90px;">
                            </div>
                            <div style="min-width:120px;">
                                <label class="text-secondary" style="font-size:0.85rem; display:block; margin-bottom:0.25rem;">Alt boşluk</label>
                                @php $mb = (int) old('label_margin_bottom', $qr['label_margin_bottom'] ?? 8); @endphp
                                <input type="number"
                                       name="label_margin_bottom"
                                       id="labelMarginBottom"
                                       class="form-input"
                                       min="0"
                                       max="40"
                                       step="1"
                                       value="{{ $mb }}"
                                       style="max-width:90px;">
                            </div>
                        </div>
                        @error('label_text')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        @error('label_align')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        @error('label_color')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        @error('label_font')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        @error('label_font_size')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        @error('label_position')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                        <button type="submit" class="btn btn-outline" name="reset" value="1">Varsayılana döndür</button>
                    </div>
                </form>
            </div>

            <div style="flex:0 0 260px; max-width:260px;">
                <h3 class="card-title-sm">QR Önizleme</h3>
                <p class="text-secondary" style="font-size:0.85rem; margin-top:0; margin-bottom:0.75rem;">
                    Aşağıdaki örnek, tüm ürünlerde kullanılacak QR kod stilini gösterir.
                </p>
                <div style="border:1px solid #e5e7eb; border-radius:12px; padding:1rem; background:#fafafa; display:flex; align-items:center; justify-content:center;">
                    <img id="qrPreviewImage"
                         src="{{ route('setting.preview') }}?t={{ time() }}"
                         data-base="{{ route('setting.preview') }}"
                         alt="QR kod önizleme"
                         width="220"
                         height="220"
                         style="display:block; max-width:100%; height:auto;">
                </div>
            </div>
        </div>
    </div>
    <script>
    (function () {
        const img = document.getElementById('qrPreviewImage');
        if (!img) return;

        const fgInput = document.getElementById('qrForeground');
        const bgInput = document.getElementById('qrBackground');
        const textInput = document.getElementById('labelText');
        const alignSelect = document.querySelector('select[name=\"label_align\"]');
        const labelColorInput = document.querySelector('input[name=\"label_color\"]');
        const fontSelect = document.querySelector('select[name=\"label_font\"]');
        const fontSizeInput = document.querySelector('select[name=\"label_font_size\"]');
        const positionSelect = document.querySelector('select[name=\"label_position\"]');
        const marginTopInput = document.getElementById('labelMarginTop');
        const marginBottomInput = document.getElementById('labelMarginBottom');

        let previewTimeout = null;

        function updatePreviewImmediate() {
            const base = img.dataset.base;
            const params = new URLSearchParams();
            if (fgInput) params.set('fg', fgInput.value || '');
            if (bgInput) params.set('bg', bgInput.value || '');
            if (textInput) params.set('text', (textInput.value || '').trim());
            if (alignSelect) params.set('align', alignSelect.value || 'center');
            if (labelColorInput) params.set('lc', labelColorInput.value || '');
            if (fontSelect) params.set('font', fontSelect.value || 'dm_sans');
            if (fontSizeInput) params.set('fs', fontSizeInput.value || '16');
            if (positionSelect) params.set('pos', positionSelect.value || 'bottom');
            if (marginTopInput) params.set('mt', marginTopInput.value || '8');
            if (marginBottomInput) params.set('mb', marginBottomInput.value || '8');
            params.set('t', Date.now().toString());
            img.src = base + '?' + params.toString();
        }

        function schedulePreview() {
            if (previewTimeout) {
                clearTimeout(previewTimeout);
            }
            previewTimeout = setTimeout(updatePreviewImmediate, 120);
        }

        [fgInput, bgInput, textInput, alignSelect, labelColorInput, fontSelect, fontSizeInput, positionSelect, marginTopInput, marginBottomInput].forEach(function (el) {
            if (!el) return;
            const evt = el.tagName === 'SELECT' ? 'change' : 'input';
            el.addEventListener(evt, schedulePreview);
        });

        // Sayfa ilk açıldığında, mevcut kaydedilmiş değerlere göre hemen göster
        updatePreviewImmediate();
    })();
    </script>
@endsection
