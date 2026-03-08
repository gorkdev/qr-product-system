<div>
    <div class="card">
        <div class="filter-bar">
            <input type="search" wire:model.debounce.300ms="search" class="form-input filter-search"
                placeholder="Ürün adıyla ara...">
        </div>

        <div class="table-wrap" wire:loading.class="table-loading">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Görsel</th>
                        <th>Ürün Adı</th>
                        <th class="th-actions">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr wire:key="product-{{ $product->id }}">
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
                            <td class="td-actions">
                                <a href="{{ route('product.show', $product->share_token) }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm" title="Ürün linkini görüntüle">
                                    <x-heroicon-o-link class="btn-icon" />
                                </a>
                                <a href="{{ route('product.edit', $product->uuid) }}" class="btn btn-outline btn-sm" wire:navigate>
                                    <x-heroicon-o-pencil-square class="btn-icon" /> Düzenle
                                </a>
                                <button type="button" class="btn btn-outline btn-sm btn-danger" wire:click="confirmDelete({{ $product->id }})">
                                    <x-heroicon-o-trash class="btn-icon" /> Sil
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="table-empty">Henüz ürün bulunmuyor.</td>
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
</div>
