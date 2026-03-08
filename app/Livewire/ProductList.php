<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use App\Models\Setting;
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
        }
    }

    public function render()
    {
        $products = Product::query()
            ->select(['id', 'uuid', 'name', 'images', 'share_token', 'created_at', 'updated_at'])
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
                } catch (\Throwable) {}
            } else {
                $product->product_link = null;
                $product->link_href = null;
            }
            return $product;
        });

        return view('livewire.product-list', ['products' => $products]);
    }
}
