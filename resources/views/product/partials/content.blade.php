<article class="public-product">
    @if(($images = $product->images ?? []) && count($images) > 0)
        <img src="{{ $images[0] }}" alt="{{ $product->name }}" class="public-product__cover" loading="eager">
    @endif

    <div class="public-product__body">
        <h1 class="public-product__title">{{ $product->name }}</h1>
        <div class="public-product__desc">{{ $product->description ?? '' }}</div>

        @if(count($images) > 1)
            <section class="public-product__section">
                <h2 class="public-product__section-title">Görseller</h2>
                <div class="public-product__gallery">
                    @foreach(array_slice($images, 1) as $img)
                        <img src="{{ $product->thumbnailUrlFromFull($img) }}" data-src="{{ $img }}" alt="" loading="lazy" onerror="this.onerror=null;this.src=this.dataset.src">
                    @endforeach
                </div>
            </section>
        @endif

        @if($videos = array_filter($product->videos ?? []))
            <section class="public-product__section">
                <h2 class="public-product__section-title">Videolar</h2>
                <div class="public-product__videos">
                    @foreach($videos as $url)
                        @php
                            $vid = preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|[^\/]*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $m) ? ($m[1] ?? null) : null;
                        @endphp
                        @if($vid)
                            <iframe src="https://www.youtube.com/embed/{{ $vid }}" class="public-product__video" allowfullscreen></iframe>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        @if($product->pdf_path)
            <section class="public-product__section">
                <h2 class="public-product__section-title">Döküman</h2>
                <a href="{{ $product->pdf_path }}" target="_blank" rel="noopener" class="public-product__pdf">PDF İndir</a>
            </section>
        @endif
    </div>
</article>
