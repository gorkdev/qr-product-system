<div x-data="{ qrModal: { open: false, name: '', qrUrl: '', productLink: '', uuid: '' }, qrImgLoaded: false, qrImgError: false }">
    <div class="card">
        <div class="filter-bar">
            <div class="filter-group">
                <input type="search" wire:model.live.debounce.250ms="search" class="form-input filter-input"
                    placeholder="Ürün adıyla ara...">
            </div>
            <div class="filter-group">
                <x-custom-select
                    wire:model.live="sortBy"
                    :options="['created_at' => 'Eklenme tarihi', 'updated_at' => 'Son güncelleme', 'name' => 'Ürün adı', 'visits_count' => 'Ziyaret sayısı']"
                    placeholder="Sırala" />
            </div>
            <div class="filter-group">
                <x-custom-select
                    wire:model.live="sortDir"
                    :options="['desc' => 'Azalan', 'asc' => 'Artan']"
                    placeholder="Yön" />
            </div>
        </div>

        <div class="table-wrap table-wrap--fit" wire:loading.class="table-loading">
            <table class="data-table data-table--modern data-table--full data-table--products">
                <colgroup>
                    <col class="col-thumb">
                    <col class="col-name">
                    <col class="col-visits">
                    <col class="col-date">
                    <col class="col-date">
                    <col class="col-actions">
                </colgroup>
                <thead>
                    <tr>
                        <th>Görsel</th>
                        <th>Ürün Adı</th>
                        <th>Ziyaret</th>
                        <th>Eklenme Tarihi</th>
                        <th>Son Güncelleme</th>
                        <th class="th-actions">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr wire:key="product-{{ $product->id }}"
                            data-qr-url="{{ $product->qr_url ?? '' }}"
                            data-product-link="{{ $product->product_link ?? '' }}"
                            data-link-href="{{ $product->link_href ?? '' }}"
                            data-product-name="{{ e($product->name) }}"
                            data-product-uuid="{{ $product->uuid ?? '' }}">
                            <td>
                                @php
                                    $fullImg = ($product->images ?? [])[0] ?? null;
                                    $thumb = $product->main_thumbnail;
                                @endphp
                                @if($fullImg)
                                    <img src="{{ $thumb }}" data-src-full="{{ $fullImg }}" alt="" class="table-thumb" width="48" height="48" loading="lazy" decoding="async" onerror="this.onerror=null;this.src=this.dataset.srcFull||this.src">
                                @else
                                    <span class="table-thumb-placeholder">
                                        <x-heroicon-o-photo class="placeholder-icon" />
                                    </span>
                                @endif
                            </td>
                            <td>{{ $product->name }}</td>
                            <td class="table-date">{{ number_format($product->visits_count ?? 0) }}</td>
                            <td class="table-date">{{ format_date_modern($product->created_at) }}</td>
                            <td class="table-date">{{ format_date_modern($product->updated_at) }}</td>
                            <td class="td-actions-cell">
                                <div class="td-actions">
                                @if($product->share_token)
                                <button type="button" class="btn btn-outline btn-sm"
                                    @if($product->qr_url)
                                    @@click="qrModal = { open: true, name: $event.target.closest('tr').dataset.productName, qrUrl: $event.target.closest('tr').dataset.qrUrl, productLink: $event.target.closest('tr').dataset.productLink, uuid: $event.target.closest('tr').dataset.productUuid }; qrImgLoaded = false; qrImgError = false"
                                    @else
                                    disabled
                                    @endif
                                    title="{{ $product->qr_url ? 'QR Kodunu görüntüle' : 'QR kod mevcut değil' }}">
                                    <x-heroicon-o-squares-2x2 class="btn-icon" />
                                </button>
                                <a href="{{ $product->link_href ?? '#' }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm" title="Ürün linkini görüntüle">
                                    <x-heroicon-o-link class="btn-icon" />
                                </a>
                                @endif
                                <a href="{{ route('product.edit', $product->uuid) }}" class="btn btn-outline btn-sm" wire:navigate>
                                    <x-heroicon-o-pencil-square class="btn-icon" /> Düzenle
                                </a>
                                <button type="button" class="btn btn-outline btn-sm btn-danger" wire:click="confirmDelete({{ $product->id }})">
                                    <x-heroicon-o-trash class="btn-icon" /> Sil
                                </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-empty">Henüz ürün bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="pagination-wrap">
                {{ $products->links('livewire.pagination') }}
            </div>
        @endif
    </div>

    @if($deleteProductId)
        <div class="modal-overlay" wire:click="cancelDelete">
            <div class="modal" wire:click.stop>
                <h3 class="modal-title">Ürünü Sil</h3>
                <p class="modal-text">Bu ürünü silmek istediğinize emin misiniz?</p>
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" wire:click="cancelDelete">İptal</button>
                    <button type="button" class="btn btn-primary" wire:click="deleteProduct">Sil</button>
                </div>
            </div>
        </div>
    @endif

    <div class="modal-overlay" x-show="qrModal.open" x-cloak x-transition
        @@click="qrModal.open = false">
        <div class="modal modal-qr" @click.stop>
            <h3 class="modal-title" x-text="qrModal.name || 'QR Kod'"></h3>
            <div class="modal-qr-body">
                <div class="modal-qr-img-wrap">
                    <div class="modal-qr-loader" x-show="!qrImgLoaded && qrModal.qrUrl && !qrImgError">
                        <span class="loader-spinner"></span>
                        <span>Yükleniyor...</span>
                    </div>
                    <p class="modal-qr-error" x-show="qrImgError" x-cloak>QR kodu yüklenemedi.</p>
                    <template x-if="qrModal.open && qrModal.qrUrl">
                        <img :src="qrModal.qrUrl" alt="QR Kod" width="200" height="200" class="modal-qr-img"
                            :class="{ 'modal-qr-img--loaded': qrImgLoaded }"
                            @@load="qrImgLoaded = true; qrImgError = false"
                            @@error="qrImgLoaded = true; qrImgError = true">
                    </template>
                </div>
                <a x-show="qrImgLoaded && !qrImgError" :href="qrModal.qrUrl" :download="'qr-' + (qrModal.uuid || '') + '.png'" class="btn btn-primary modal-qr-dl">QR Kodunu İndir</a>
                <div class="modal-qr-link-row">
                    <input type="text" class="form-input modal-qr-link-input" :value="qrModal.productLink" readonly>
                    <button type="button" class="btn btn-outline btn-sm modal-qr-copy"
                        @@click="navigator.clipboard.writeText(qrModal.productLink).then(() => { $el.textContent='Kopyalandı'; setTimeout(() => $el.textContent='Kopyala', 1500) })">
                        Kopyala
                    </button>
                </div>
            </div>
            <div class="modal-actions" style="margin-top: 1rem;">
                <button type="button" class="btn btn-outline" @@click="qrModal.open = false">Kapat</button>
            </div>
        </div>
    </div>
</div>
