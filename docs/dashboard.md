# Dashboard

Bu belge, admin paneli ana sayfasını (Dashboard) açıklar.  
**Ana referans:** [PROJECT_STRUCTURE.md](../PROJECT_STRUCTURE.md)

---

## Genel Bakış

- **URL:** `/` (root)
- **Route:** `admin.dashboard` → `DashboardController::__invoke()`
- **View:** `admin.dashboard`

---

## Controller: DashboardController

**Dosya:** `app/Http/Controllers/DashboardController.php`

### Hesaplanan Veriler

| Değişken | Sorgu | Açıklama |
|----------|-------|----------|
| productCount | `Product::count()` | Toplam ürün sayısı |
| totalVisits | `ProductVisit::count()` | Toplam ziyaret |
| visitsToday | `ProductVisit::whereDate('visited_at', today())` | Bugünkü ziyaretler |
| visitsThisWeek | `ProductVisit::where('visited_at', '>=', now()->startOfWeek())` | Bu hafta |
| visitsThisMonth | `ProductVisit::where('visited_at', '>=', now()->startOfMonth())` | Bu ay |
| topProducts | `Product::withCount('visits')->orderByDesc('visits_count')->limit(5)` | En çok ziyaret edilen 5 ürün |

---

## View: admin.dashboard

**Dosya:** `resources/views/admin/dashboard.blade.php`

### Bölümler

1. **stats-row (istatistik kartları)**
   - Toplam Ürün (productCount)
   - Toplam Ziyaret (totalVisits)
   - Bugün (visitsToday)
   - Bu Hafta (visitsThisWeek)
   - Bu Ay (visitsThisMonth)

2. **En Çok Ziyaret Edilen Ürünler**
   - `@if($topProducts->isNotEmpty())` ile gösterilir
   - Tablo: Ürün adı, ziyaret sayısı, Düzenle linki

3. **Yardım metni**
   - Menü kullanımı hakkında bilgi

---

## Layout

- `layouts.admin` extend edilir
- Sidebar: Dashboard, Ürünler, Yeni Ürün, Ziyaret Logları, Ayarlar
