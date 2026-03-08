<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    public string $search = '';

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
            ->select(['id', 'uuid', 'name', 'images'])
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('livewire.product-list', ['products' => $products]);
    }
}
