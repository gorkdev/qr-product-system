@php
    $images = $product->images ?? [];
    $hasCover = count($images) > 0;
@endphp

<div class="product-page">

    <article class="product-card {{ !$hasCover ? 'product-no-cover' : '' }}">
        {{-- Hero Cover --}}
        @if($hasCover)
            <header class="product-hero">
                <img src="{{ $images[0] }}" alt="{{ $product->name }}" class="product-hero__img" loading="eager" fetchpriority="high">
                <div class="product-hero__overlay"></div>
            </header>
        @else
            <header class="product-hero">
                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 9' fill='%23a8a29e'%3E%3Crect width='16' height='9' fill='%23d6d3d1'/%3E%3C/svg%3E" alt="" class="product-hero__img" aria-hidden="true">
            </header>
        @endif

        <div class="product-body">
            <h1 class="product-title">{{ $product->name }}</h1>
            <div class="product-desc">{{ $product->description ?? '' }}</div>

            @if(count($images) > 1)
                <section class="product-section" aria-labelledby="gallery-heading">
                    <h2 id="gallery-heading" class="product-section__title">Görseller</h2>
                    <div class="product-gallery">
                        @foreach(array_slice($images, 1) as $img)
                            <a href="{{ $img }}" target="_blank" rel="noopener" class="product-gallery__item" title="Tam boyutta görüntüle">
                                <img src="{{ $product->thumbnailUrlFromFull($img) }}" data-src="{{ $img }}" alt="{{ $product->name }} görsel {{ $loop->iteration }}" loading="lazy" onerror="this.onerror=null;this.src=this.dataset.src||this.src">
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
</div>
