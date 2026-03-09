@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="dashboard-stats">
    <div class="stats-row" style="padding: 0 !important;">
        <div class="stat-card stat-card--highlight">
            <span class="stat-value">{{ number_format($productCount) }}</span>
            <span class="stat-label">Toplam Ürün</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ number_format($totalVisits) }}</span>
            <span class="stat-label">Toplam Ziyaret</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ number_format($visitsToday) }}</span>
            <span class="stat-label">Bugün</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ number_format($visitsThisWeek) }}</span>
            <span class="stat-label">Bu Hafta</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ number_format($visitsThisMonth) }}</span>
            <span class="stat-label">Bu Ay</span>
        </div>
    </div>

    @if($topProducts->isNotEmpty())
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <h3 class="card-title-sm">En Çok Ziyaret Edilen Ürünler</h3>
            <div class="table-wrap">
                <table class="data-table data-table--modern">
                    <thead>
                        <tr>
                            <th>Ürün</th>
                            <th>Ziyaret</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topProducts as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ number_format($product->visits_count) }}</td>
                            <td>
                                <a href="{{ route('product.edit', $product->uuid) }}" class="btn btn-outline btn-sm" wire:navigate>Düzenle</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-body">
            <p class="text-secondary">Soldan menüyü kullanarak ürün ekleyebilir, ziyaret loglarını inceleyebilirsiniz.</p>
        </div>
    </div>

    <div class="card" style="margin-top: 1.5rem;" x-data>
        <div class="card-body">
            <h3 class="card-title-sm">Toast Test</h3>
            <p class="text-secondary" style="margin-bottom: 1rem;">Toast bileşenini test etmek için butonlara tıklayın.</p>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                <button type="button" class="btn btn-primary" @click="$store.toast.show('success', 'İşlem başarıyla tamamlandı!')">
                    Success Toast
                </button>
                <button type="button" class="btn btn-danger" @click="$store.toast.show('error', 'Bir hata oluştu!')">
                    Error Toast
                </button>
                <button type="button" class="btn btn-outline" @click="$store.toast.show('waiting', 'İşlem devam ediyor, lütfen bekleyin...')">
                    Waiting Toast
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
