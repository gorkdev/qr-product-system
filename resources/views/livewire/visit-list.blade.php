<div>
    <div class="card">
        <div class="filter-bar filter-bar--multi">
            <div class="filter-group">
                <input type="search" wire:model.live.debounce.250ms="search" class="form-input filter-input"
                    placeholder="IP, tarayıcı, cihaz, konum ara...">
            </div>
            <div class="filter-group">
                <x-custom-select
                    wire:model.live="productFilter"
                    :options="['' => 'Tüm ürünler'] + $products->pluck('name', 'id')->toArray()"
                    placeholder="Ürün" />
            </div>
            <div class="filter-group">
                <x-custom-select
                    wire:model.live="deviceFilter"
                    :options="['' => 'Tüm cihazlar', 'Mobil' => 'Mobil', 'Tablet' => 'Tablet', 'Masaüstü' => 'Masaüstü']"
                    placeholder="Cihaz" />
            </div>
            <div class="filter-group">
                <x-custom-select
                    wire:model.live="dateRange"
                    :options="['' => 'Tüm zamanlar', 'today' => 'Bugün', 'week' => 'Son 7 gün', 'month' => 'Son 30 gün', 'quarter' => 'Son 3 ay', 'year' => 'Son 1 yıl']"
                    placeholder="Tarih aralığı" />
            </div>
            <div class="filter-group">
                <input type="date" wire:model.live="dateFrom" class="form-input filter-input" title="Başlangıç" placeholder="Başlangıç">
            </div>
            <div class="filter-group">
                <input type="date" wire:model.live="dateTo" class="form-input filter-input" title="Bitiş" placeholder="Bitiş">
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <span class="stat-value">{{ number_format($visits->total()) }}</span>
                <span class="stat-label">Toplam ziyaret (filtrelenmiş)</span>
            </div>
            <div class="stat-card">
                <span class="stat-value">{{ number_format($totalVisits) }}</span>
                <span class="stat-label">Tüm zamanlar toplam</span>
            </div>
        </div>

        <div class="table-wrap" wire:loading.class="table-loading">
            <table class="data-table data-table--modern data-table--full data-table--equal-cols">
                <thead>
                    <tr>
                        <th>Ürün</th>
                        <th>IP</th>
                        <th>Konum</th>
                        <th>Cihaz</th>
                        <th>Tarayıcı</th>
                        <th>Durum</th>
                        <th>Tarih</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visits as $visit)
                        <tr wire:key="visit-{{ $visit->id }}">
                            <td>{{ $visit->product?->name ?? '-' }}</td>
                            <td><code class="table-code">{{ $visit->ip_address ?? '-' }}</code></td>
                            <td>
                            @if($visit->is_anonymous)
                                <span class="table-muted">-</span>
                            @else
                                @php
                                    $parts = array_filter([$visit->city, $visit->region_name, $visit->country]);
                                @endphp
                                {{ $parts ? implode(', ', $parts) : '-' }}
                            @endif
                        </td>
                            <td>{{ $visit->device_type ?? '-' }}</td>
                            <td>{{ $visit->browser ?? '-' }}</td>
                            <td>
                                @if($visit->is_anonymous && $visit->error_message)
                                    <span class="table-error-badge" title="{{ e($visit->error_message) }}">Hata</span>
                                @else
                                    <span class="table-ok">OK</span>
                                @endif
                            </td>
                            <td class="table-date">{{ format_date_modern($visit->visited_at) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-empty">Henüz ziyaret kaydı yok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($visits->hasPages())
            <div class="pagination-wrap">
                {{ $visits->links('livewire.pagination') }}
            </div>
        @endif
    </div>
</div>
