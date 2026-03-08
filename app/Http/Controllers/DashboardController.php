<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVisit;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $productCount = Product::count();
        $totalVisits = ProductVisit::count();
        $visitsToday = ProductVisit::whereDate('visited_at', today())->count();
        $visitsThisWeek = ProductVisit::where('visited_at', '>=', now()->startOfWeek())->count();
        $visitsThisMonth = ProductVisit::where('visited_at', '>=', now()->startOfMonth())->count();
        $topProducts = Product::withCount('visits')->orderByDesc('visits_count')->limit(5)->get();

        return view('admin.dashboard', [
            'productCount' => $productCount,
            'totalVisits' => $totalVisits,
            'visitsToday' => $visitsToday,
            'visitsThisWeek' => $visitsThisWeek,
            'visitsThisMonth' => $visitsThisMonth,
            'topProducts' => $topProducts,
        ]);
    }
}
