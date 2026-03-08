<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVisit;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    private function isQrAccessAllowed(Request $request): bool
    {
        $mode = Setting::get('access_mode', 'link');
        if ($mode === 'link') {
            return true;
        }
        return $request->query('ref') === 'qr';
    }

    /**
     * Tek giriş noktası - loader, yönlendirme ve içerik aynı sayfada.
     * URL: urun-bilgisi/{share_token}
     */
    public function gate(string $share_token, Request $request)
    {
        $product = Product::where('share_token', $share_token)->firstOrFail();
        $mode = Setting::get('access_mode', 'link');

        $showContent = $mode === 'qr_only' && session('product_entered_' . $share_token);
        if ($showContent) {
            return view('product.landing', [
                'product' => $product,
                'showRedirect' => false,
                'showContent' => true,
            ]);
        }

        if (! $this->isQrAccessAllowed($request)) {
            return view('product.qr-only');
        }

        return view('product.landing', [
            'product' => $product,
            'showRedirect' => true,
            'showContent' => false,
        ]);
    }

    /**
     * Ziyaret kaydı - API ile konum al, DB'ye kaydet. JSON döner.
     */
    public function saveVisit(string $share_token, Request $request): JsonResponse
    {
        $product = Product::where('share_token', $share_token)->firstOrFail();

        try {
            $ip = $this->getClientIpForGeo($request);
            $location = [];
            try {
                $location = $this->getDetailedLocationFromIp($ip);
            } catch (\Throwable) {}

            ProductVisit::create([
                'product_id' => $product->id,
                'ip_address' => $ip,
                'user_agent' => $request->userAgent(),
                'device_type' => $this->parseDeviceType($request->userAgent()),
                'browser' => $this->parseBrowser($request->userAgent()),
                'platform' => $this->parsePlatform($request->userAgent()),
                'city' => $location['city'] ?? null,
                'country' => $location['country'] ?? null,
                'region_name' => $location['regionName'] ?? null,
                'timezone' => $location['timezone'] ?? null,
                'isp' => $location['isp'] ?? null,
                'lat' => $location['lat'] ?? null,
                'lon' => $location['lon'] ?? null,
                'visited_at' => now(),
            ]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            try {
                $ip = $this->getClientIpForGeo($request);
                ProductVisit::create([
                    'product_id' => $product->id,
                    'ip_address' => $ip ?? $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'device_type' => $this->parseDeviceType($request->userAgent()),
                    'browser' => $this->parseBrowser($request->userAgent()),
                    'platform' => $this->parsePlatform($request->userAgent()),
                    'visited_at' => now(),
                    'error_message' => $e->getMessage(),
                    'is_anonymous' => false,
                ]);
            } catch (\Throwable) {}
            return response()->json(['success' => true]);
        }
    }

    /**
     * Hata durumunda anonim kayıt - error_message ile
     */
    public function saveVisitAnonymous(string $share_token, Request $request): JsonResponse
    {
        $product = Product::where('share_token', $share_token)->firstOrFail();

        try {
            ProductVisit::create([
                'product_id' => $product->id,
                'ip_address' => null,
                'user_agent' => $request->userAgent(),
                'device_type' => $this->parseDeviceType($request->userAgent()),
                'browser' => $this->parseBrowser($request->userAgent()),
                'platform' => $this->parsePlatform($request->userAgent()),
                'city' => null,
                'country' => null,
                'visited_at' => now(),
                'error_message' => $request->input('error', 'Bilinmeyen hata'),
                'is_anonymous' => true,
            ]);

            return response()->json(['success' => true]);
        } catch (\Throwable) {
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Session set et. AJAX ise JSON döner (aynı sayfa içerik gösterir), değilse gate'e redirect.
     */
    public function confirmEnter(string $share_token, Request $request)
    {
        $product = Product::where('share_token', $share_token)->firstOrFail();
        session(['product_entered_' . $share_token => true]);
        session()->save();

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true]);
        }

        return redirect()->route('product.gate', $share_token);
    }

    /**
     * Konum API'si için kullanılacak IP. Proxy/yerel ortamda X-Forwarded-For vb. dener.
     */
    private function getClientIpForGeo(Request $request): ?string
    {
        $ip = $request->ip();
        if ($ip && ! $this->isPrivateOrLocalIp($ip)) {
            return $ip;
        }
        foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CF_CONNECTING_IP'] as $header) {
            $val = $request->server($header);
            if (! $val) continue;
            $ips = array_map('trim', explode(',', (string) $val));
            $candidate = $ips[0] ?? null;
            if ($candidate && ! $this->isPrivateOrLocalIp($candidate)) {
                return $candidate;
            }
        }
        return $ip;
    }

    private function isPrivateOrLocalIp(?string $ip): bool
    {
        if (! $ip) return true;
        if (in_array($ip, ['127.0.0.1', '::1'], true)) return true;
        if (str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) return true;
        if (preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip)) return true;
        return false;
    }

    private function getLocationFromIp(?string $ip): array
    {
        if (! $ip || $ip === '127.0.0.1' || str_starts_with($ip, '192.168.')) {
            return [null, null];
        }

        try {
            $res = Http::timeout(2)->get("http://ip-api.com/json/{$ip}?fields=city,country");
            if ($res->successful()) {
                $d = $res->json();
                return [$d['city'] ?? null, $d['country'] ?? null];
            }
        } catch (\Throwable) {}

        return [null, null];
    }

    /**
     * ip-api.com ile konum bilgisi
     */
    private function getDetailedLocationFromIp(?string $ip): array
    {
        if (! $ip || $this->isPrivateOrLocalIp($ip)) {
            return [];
        }

        try {
            $res = Http::timeout(5)->get("http://ip-api.com/json/{$ip}");

            if ($res->successful()) {
                $d = $res->json();
                if (($d['status'] ?? '') !== 'success') {
                    return [];
                }
                return [
                    'country' => $d['country'] ?? null,
                    'regionName' => $d['regionName'] ?? null,
                    'city' => $d['city'] ?? null,
                    'timezone' => $d['timezone'] ?? null,
                    'isp' => $d['isp'] ?? null,
                    'lat' => $d['lat'] ?? null,
                    'lon' => $d['lon'] ?? null,
                ];
            }
        } catch (\Throwable) {}

        return [];
    }

    private function parseDeviceType(?string $ua): string
    {
        if (! $ua) return '-';
        if (stripos($ua, 'Mobile') !== false && stripos($ua, 'Tablet') === false) return 'Mobil';
        if (stripos($ua, 'Tablet') !== false || stripos($ua, 'iPad') !== false) return 'Tablet';
        return 'Masaüstü';
    }

    private function parseBrowser(?string $ua): string
    {
        if (! $ua) return '-';
        if (stripos($ua, 'Edg/') !== false) return 'Edge';
        if (stripos($ua, 'Chrome') !== false) return 'Chrome';
        if (stripos($ua, 'Firefox') !== false) return 'Firefox';
        if (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false) return 'Safari';
        return 'Diğer';
    }

    private function parsePlatform(?string $ua): string
    {
        if (! $ua) return '-';
        if (stripos($ua, 'Windows') !== false) return 'Windows';
        if (stripos($ua, 'Mac') !== false || stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) return 'Apple';
        if (stripos($ua, 'Android') !== false) return 'Android';
        if (stripos($ua, 'Linux') !== false) return 'Linux';
        return 'Diğer';
    }

    public function index()
    {
        return view('admin.product-index');
    }

    public function create()
    {
        return view('admin.product-create');
    }

    public function edit(Product $product)
    {
        return view('admin.product-edit', ['product' => $product]);
    }

    public function update(Request $request, Product $product)
    {
        // Update handled by Livewire form; this route exists for completeness
        return redirect()->route('product.edit', $product->uuid);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('product.index')->with('success', 'Ürün silindi.');
    }
}
