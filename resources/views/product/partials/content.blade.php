@php
    $images = $product->images ?? [];
    $hasCover = count($images) > 0;
@endphp

<div class="product-page">

    <article class="product-card {{ !$hasCover ? 'product-no-cover' : '' }}">
        {{-- Hero Cover --}}
        @if($hasCover)
            <header class="product-hero">
                <img src="{{ $images[0] }}"
                     alt="{{ $product->name }}"
                     class="product-hero__img product-hero-trigger"
                     data-full="{{ $images[0] }}"
                     loading="eager"
                     fetchpriority="high">
                <div class="product-hero__overlay"></div>
            </header>
        @else
            <header class="product-hero">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 9' fill='%23a8a29e'%3E%3Crect width='16' height='9' fill='%23d6d3d1'/%3E%3C/svg%3E" alt="" class="product-hero__img" aria-hidden="true">
            </header>
        @endif

        @php
            $shareUrl = urlencode(request()->fullUrl());
            $shareLine = urlencode('Şuna bir göz at ▼▼');
        @endphp

        <div class="product-body">
            <div class="product-header-row">
                <div class="product-header-main">
                    <h1 class="product-title">{{ $product->name }}</h1>
                    <div class="product-desc">{{ $product->description ?? '' }}</div>
                </div>
                <div class="product-share" aria-label="Bu ürünü paylaş">
                    <span class="product-share__label">Paylaş</span>
                    <div class="product-share__buttons">
                        <a class="product-share__btn product-share__btn--whatsapp"
                           href="https://wa.me/?text={{ $shareLine }}%0A{{ $shareUrl }}"
                           target="_blank" rel="noopener">
                            <span class="product-share__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2C6.48 2 2 6.02 2 11.1c0 1.79.53 3.46 1.45 4.9L2 22l6.18-1.99C9.4 20.55 10.68 20.8 12 20.8 17.52 20.8 22 16.78 22 11.7 22 6.62 17.52 2 12 2zm0 2c4.07 0 7.38 3.15 7.38 7.2 0 4.06-3.31 7.2-7.38 7.2-.99 0-1.96-.2-2.85-.6l-.2-.1-3.63 1.18 1.18-3.33-.13-.21A7.01 7.01 0 0 1 4.62 11.1C4.62 7.15 7.93 4 12 4zm-3 3.2c-.17 0-.44.05-.67.3-.23.25-.88.86-.88 2.08 0 1.22.9 2.4 1.03 2.56.13.16 1.72 2.7 4.18 3.67 2.06.81 2.48.73 2.92.69.44-.04 1.44-.59 1.64-1.16.2-.57.2-1.06.14-1.16-.06-.1-.23-.16-.48-.28-.25-.12-1.44-.71-1.66-.79-.22-.08-.38-.12-.54.12-.16.24-.62.79-.76.95-.14.16-.28.18-.52.06-.24-.12-1.02-.38-1.95-1.21-.72-.64-1.21-1.43-1.35-1.67-.14-.24-.01-.37.1-.49.1-.1.24-.27.36-.4.12-.13.16-.22.24-.37.08-.16.04-.3-.02-.42-.06-.12-.54-1.28-.74-1.75-.18-.42-.37-.43-.53-.44h-.03z" />
                                </svg>
                            </span>
                            <span class="product-share__text">WhatsApp</span>
                        </a>
                        <a class="product-share__btn product-share__btn--twitter"
                           href="https://twitter.com/intent/tweet?text={{ $shareLine }}%0A{{ urlencode($product->name) }}&url={{ $shareUrl }}"
                           target="_blank" rel="noopener">
                            <span class="product-share__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17.21 3H20l-6.06 6.94L21.5 21h-5.3l-3.72-4.87L8 21H5.21l6.45-7.39L3 3h5.39l3.35 4.44L17.21 3zm-1.28 15.03h1.47L8.15 4.9H6.57l9.36 13.13z"/>
                                </svg>
                            </span>
                            <span class="product-share__text">Twitter</span>
                        </a>
                        <a class="product-share__btn product-share__btn--facebook"
                           href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}"
                           target="_blank" rel="noopener">
                            <span class="product-share__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M13 10.5V8.75c0-.84.28-1.4 1.65-1.4H17V4h-2.7C10.9 4 9.5 5.64 9.5 8.35v2.15H7v3h2.5V20h3.5v-6.5H16l.5-3H13z"/>
                                </svg>
                            </span>
                            <span class="product-share__text">Facebook</span>
                        </a>
                    </div>
                </div>
            </div>

            @if(count($images) > 1)
                <section class="product-section" aria-labelledby="gallery-heading">
                    <h2 id="gallery-heading" class="product-section__title">Görseller</h2>
                    <div class="product-gallery">
                        @foreach(array_slice($images, 1) as $img)
                            <a href="{{ $img }}"
                               class="product-gallery__item"
                               data-full="{{ $img }}"
                               title="Tam boyutta görüntüle">
                                <img src="{{ $product->thumbnailUrlFromFull($img) }}"
                                     data-src="{{ $img }}"
                                     alt="{{ $product->name }} görsel {{ $loop->iteration }}"
                                     loading="lazy"
                                     onerror="this.onerror=null;this.src=this.dataset.src||this.src">
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($videos = array_filter($product->videos ?? []))
                <section class="product-section" aria-labelledby="videos-heading">
                    <h2 id="videos-heading" class="product-section__title">Videolar</h2>
                    <div class="product-videos">
                        @foreach($videos as $url)
                            @php
                                $vid = preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|[^\/]*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $m) ? ($m[1] ?? null) : null;
                            @endphp
                            @if($vid)
                                <div class="product-video-wrap">
                                    <iframe src="https://www.youtube.com/embed/{{ $vid }}" title="YouTube video" allowfullscreen loading="lazy"></iframe>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif

            @if($product->pdf_path)
                <section class="product-section" aria-labelledby="pdf-heading">
                    <h2 id="pdf-heading" class="product-section__title">Döküman</h2>
                    <a href="{{ $product->pdf_path }}" target="_blank" rel="noopener" class="product-pdf">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        PDF İndir
                    </a>
                </section>
            @endif
        </div>
    </article>

    {{-- Görsel Önizleme Modali --}}
    <div class="product-image-modal" id="productImageModal" aria-hidden="true">
        <div class="product-image-modal__backdrop" data-modal-close></div>
        <div class="product-image-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="productImageModalTitle">
            <button type="button" class="product-image-modal__close" data-modal-close aria-label="Kapat">
                ✕
            </button>
            <div class="product-image-modal__body">
                <img id="productImageModalImg" src="" alt="Ürün görseli" loading="lazy">
            </div>
            <div class="product-image-modal__footer">
                <a id="productImageModalDownload" href="#" download class="product-image-modal__download">
                    Görseli İndir
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var galleryItems = document.querySelectorAll('.product-gallery__item, .product-hero-trigger');
    var modal = document.getElementById('productImageModal');
    if (!modal || !galleryItems.length) return;

    var imgEl = document.getElementById('productImageModalImg');
    var downloadEl = document.getElementById('productImageModalDownload');

    function openModal(fullUrl) {
        if (!fullUrl) return;
        imgEl.src = fullUrl;
        downloadEl.href = fullUrl;
        try {
            var urlObj = new URL(fullUrl, window.location.href);
            var filename = (urlObj.pathname.split('/').pop() || 'image').split('?')[0];
            downloadEl.setAttribute('download', filename);
        } catch (e) {
            downloadEl.setAttribute('download', 'image');
        }
        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('product-image-modal--open');
        document.body.classList.add('product-image-modal-open');
    }

    function closeModal() {
        modal.setAttribute('aria-hidden', 'true');
        modal.classList.remove('product-image-modal--open');
        document.body.classList.remove('product-image-modal-open');
    }

    galleryItems.forEach(function (item) {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            var full = item.getAttribute('data-full') || item.getAttribute('href');
            openModal(full);
        });
    });

    modal.querySelectorAll('[data-modal-close]').forEach(function (el) {
        el.addEventListener('click', function () {
            closeModal();
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
});
</script>
