<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'created_at';

    public string $sortDir = 'desc';

    public ?int $deleteProductId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteProductId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deleteProductId = null;
    }

    public function deleteProduct(): void
    {
        if ($this->deleteProductId) {
            Product::where('id', $this->deleteProductId)->delete();
            $this->deleteProductId = null;
            $this->dispatch('toast', type: 'success', message: 'Ürün silindi.');
        }
    }

    public function placeholder(): string
    {
        $skeletonRow = '
            <tr class="skeleton-row">
                <td><span class="skeleton skeleton-thumb"></span></td>
                <td><span class="skeleton skeleton-text" style="width: 70%;"></span></td>
                <td><span class="skeleton skeleton-text" style="width: 2.5em;"></span></td>
                <td><span class="skeleton skeleton-text" style="width: 5.5em;"></span></td>
                <td><span class="skeleton skeleton-text" style="width: 5.5em;"></span></td>
                <td>
                    <span class="skeleton skeleton-btn"></span>
                    <span class="skeleton skeleton-btn"></span>
                    <span class="skeleton skeleton-btn"></span>
                </td>
            </tr>';
        $skeletonRows = str_repeat($skeletonRow, 8);

        return <<<HTML
        <div>
            <div class="card">
                <div class="filter-bar">
                    <div class="filter-group">
                        <input type="search" class="form-input filter-input" placeholder="Ürün adıyla ara..." disabled>
                    </div>
                    <div class="filter-group">
                        <div class="custom-select-wrap">
                            <button type="button" class="custom-select-trigger" disabled style="opacity: 0.7; cursor: not-allowed;">
                                <span class="custom-select-value">Sırala</span>
                                <span class="custom-select-chevron">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="filter-group">
                        <div class="custom-select-wrap">
                            <button type="button" class="custom-select-trigger" disabled style="opacity: 0.7; cursor: not-allowed;">
                                <span class="custom-select-value">Yön</span>
                                <span class="custom-select-chevron">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-wrap table-wrap--fit">
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
                            {$skeletonRows}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        HTML;
    }

    public function render()
    {
        $products = Product::query()
            ->select(['id', 'uuid', 'name', 'images', 'share_token', 'qr_path', 'created_at', 'updated_at'])
            ->withCount('visits')
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy($this->sortBy === 'visits_count' ? 'visits_count' : $this->sortBy, $this->sortDir)
            ->paginate(10)
            ->withQueryString();

        $accessMode = Setting::get('access_mode', 'link');

        $products->getCollection()->transform(function ($product) use ($accessMode) {
            $product->qr_url = null;
            if ($product->share_token) {
                $gateUrl = url(route('product.gate', $product->share_token));
                if ($accessMode === 'link') {
                    $product->product_link = $gateUrl;
                    $product->link_href = $gateUrl;
                } else {
                    $product->product_link = $gateUrl . '?ref=qr';
                    $product->link_href = $gateUrl;
                }
                try {
                    $product->qr_url = $product->getQrCodePath();
                } catch (\Throwable $e) {
                    Log::error('QR kod oluşturulamadı', [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'exception' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } else {
                $product->product_link = null;
                $product->link_href = null;
            }
            return $product;
        });

        return view('livewire.product-list', ['products' => $products]);
    }
}
