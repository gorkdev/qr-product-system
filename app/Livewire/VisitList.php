<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\ProductVisit;
use Livewire\Component;
use Livewire\WithPagination;

class VisitList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $productFilter = '';

    public string $deviceFilter = '';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public string $dateRange = '';

    public function updatedDateRange(string $value): void
    {
        $this->resetPage();
        if ($value === '') {
            $this->dateFrom = null;
            $this->dateTo = null;
            return;
        }
        $today = now()->format('Y-m-d');
        $this->dateTo = $today;
        match ($value) {
            'today' => $this->dateFrom = $today,
            'week' => $this->dateFrom = now()->subWeek()->format('Y-m-d'),
            'month' => $this->dateFrom = now()->subMonth()->format('Y-m-d'),
            'quarter' => $this->dateFrom = now()->subMonths(3)->format('Y-m-d'),
            'year' => $this->dateFrom = now()->subYear()->format('Y-m-d'),
            default => $this->dateFrom = null,
        };
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingProductFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDeviceFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $visits = ProductVisit::with('product')
            ->when($this->search !== '', fn ($q) => $q->where(fn ($q2) =>
                $q2->where('ip_address', 'like', '%' . $this->search . '%')
                    ->orWhere('user_agent', 'like', '%' . $this->search . '%')
                    ->orWhere('browser', 'like', '%' . $this->search . '%')
                    ->orWhere('device_type', 'like', '%' . $this->search . '%')
                    ->orWhere('city', 'like', '%' . $this->search . '%')
                    ->orWhere('country', 'like', '%' . $this->search . '%')
                    ->orWhere('region_name', 'like', '%' . $this->search . '%')
                    ->orWhere('error_message', 'like', '%' . $this->search . '%')
            ))
            ->when($this->productFilter !== '', fn ($q) => $q->where('product_id', $this->productFilter))
            ->when($this->deviceFilter !== '', fn ($q) => $q->where('device_type', $this->deviceFilter))
            ->when(filled($this->dateFrom), fn ($q) => $q->whereDate('visited_at', '>=', $this->dateFrom))
            ->when(filled($this->dateTo), fn ($q) => $q->whereDate('visited_at', '<=', $this->dateTo))
            ->orderByDesc('visited_at')
            ->paginate(20)
            ->withQueryString();

        $products = \App\Models\Product::orderBy('name')->get(['id', 'name']);
        $totalVisits = \App\Models\ProductVisit::count();

        return view('livewire.visit-list', [
            'visits' => $visits,
            'products' => $products,
            'totalVisits' => $totalVisits,
        ]);
    }
}
