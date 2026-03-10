<?php

namespace App\Models;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Label\Font\Font;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Label\Margin\Margin;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'images', 'videos', 'pdf_path', 'qr_path', 'share_token'];

    protected $casts = [
        'images' => 'array',
        'videos' => 'array',
    ];

    /**
     * Get storage base path: products/{uuid}/
     */
    public function getStoragePath(): string
    {
        return 'products/' . $this->uuid . '/';
    }

    /**
     * Get images path: products/{uuid}/images/
     */
    public function getImagesPath(): string
    {
        return $this->getStoragePath() . 'images/';
    }

    /**
     * Get PDF path: products/{uuid}/pdf/
     */
    public function getPdfPath(): string
    {
        return $this->getStoragePath() . 'pdf/';
    }

    /**
     * Listelerde kullanılmak üzere 128x128 thumbnail URL (yoksa ilk görsel)
     */
    public function getMainThumbnailAttribute(): ?string
    {
        $images = $this->images ?? [];
        $first = $images[0] ?? null;
        return $first ? $this->thumbnailUrlFromFull($first) : null;
    }

    /**
     * Full image URL'den thumb URL türet (images/x.jpg -> images/thumbs/x.jpg)
     */
    public function thumbnailUrlFromFull(string $fullUrl): string
    {
        $path = parse_url($fullUrl, PHP_URL_PATH);
        if (!$path || !str_contains($path, '/storage/')) {
            return $fullUrl;
        }
        $relative = preg_replace('#^/storage/#', '', $path);
        $dir = dirname($relative);
        $file = basename($relative);
        $thumbPath = $dir . '/thumbs/' . $file;
        return Storage::url($thumbPath);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function visits(): HasMany
    {
        return $this->hasMany(ProductVisit::class)->orderByDesc('visited_at');
    }

    /**
     * Ürün linki için QR kod oluşturur veya mevcut olanı döner.
     * QR dosyası storage'a kaydedilir ve yolu veritabanına yazılır.
     */
    public function getQrCodePath(): string
    {
        $path = $this->getStoragePath() . 'qr.png';
        $this->generateQrCode($path);

        return $this->storagePathToUrl($path);
    }

    public function generateQrCode(string $path, ?string $unused = null): void
    {
        Storage::disk('public')->makeDirectory(dirname($path));
        $baseUrl = url(route('product.gate', $this->share_token));
        $url = $baseUrl;

        $qrConfig = json_decode((string) Setting::get('qr_style', ''), true) ?: [];
        $foregroundHex = $qrConfig['foreground'] ?? '#111827';
        $backgroundHex = $qrConfig['background'] ?? '#ffffff';
        $labelText = trim((string) ($qrConfig['label_text'] ?? ''));
        $labelAlign = $qrConfig['label_align'] ?? 'center';
        $labelColorHex = $qrConfig['label_color'] ?? '#111827';
        $labelFontSize = max(10, min(28, (int) ($qrConfig['label_font_size'] ?? 16)));
        $labelFontKey = (string) ($qrConfig['label_font'] ?? 'dm_sans');

        $fg = $this->hexToColor($foregroundHex, new Color(17, 24, 39));
        $bg = $this->hexToColor($backgroundHex, new Color(255, 255, 255));

        $qrCode = new QrCode(
            data: $url,
            size: 300,
            margin: 10,
            foregroundColor: $fg,
            backgroundColor: $bg
        );

        $label = null;
        if ($labelText !== '') {
            $fontPath = $this->resolveFontPathForQr($labelFontKey);
            if ($fontPath && file_exists($fontPath)) {
                $labelColor = $this->hexToColor($labelColorHex, new Color(17, 24, 39));
                $alignment = LabelAlignment::tryFrom($labelAlign) ?? LabelAlignment::Center;
                $label = new Label($labelText, new Font($fontPath, $labelFontSize), $alignment, new Margin(0, 10, 10, 10), $labelColor);
            }
        }

        $writer = new PngWriter();
        $result = $writer->write($qrCode, null, $label);
        Storage::disk('public')->put($path, $result->getString());

        $this->qr_path = $path;
        $this->saveQuietly();
    }

    protected static function booted()
    {
        static::creating(function ($product) {
            $product->uuid = (string) Str::uuid();
            $product->share_token = $product->share_token ?? Str::random(64);
        });

        static::created(function ($product) {
            $product->ensureQrCodeExists();
        });

        static::retrieved(function ($product) {
            if (empty($product->share_token)) {
                $product->share_token = Str::random(64);
                $product->saveQuietly();
            }
        });

        static::deleting(function ($product) {
            Storage::disk('public')->deleteDirectory($product->getStoragePath());
        });
    }

    /**
     * QR kodunu storage'a kaydeder ve qr_path'i veritabanına yazar.
     * Ürün oluşturulduğunda otomatik çağrılır.
     */
    public function ensureQrCodeExists(): void
    {
        $path = $this->getStoragePath() . 'qr.png';
        $this->generateQrCode($path);
    }

    /**
     * Storage path'i tarayıcıda çalışan URL'e çevirir.
     * Web isteğindeyse gerçek host kullanır (APP_URL uyuşmazlığında da çalışır).
     */
    private function storagePathToUrl(string $path): string
    {
        $cleanPath = ltrim(str_replace('\\', '/', $path), '/');
        $path = '/storage/' . $cleanPath;

        if (request()->hasHeader('Host')) {
            return request()->getSchemeAndHttpHost() . $path;
        }

        return config('app.url', '') . $path;
    }

    private function hexToColor(string $hex, Color $fallback): Color
    {
        if (!preg_match('/^#([0-9a-fA-F]{6})$/', $hex, $m)) {
            return $fallback;
        }
        $int = hexdec($m[1]);
        $r = ($int >> 16) & 255;
        $g = ($int >> 8) & 255;
        $b = $int & 255;
        return new Color($r, $g, $b);
    }

    /**
     * QR kod üzerindeki etiket için font dosya yolunu çözer.
     */
    private function resolveFontPathForQr(string $key): ?string
    {
        $key = strtolower($key);
        $default = base_path('vendor/endroid/qr-code/assets/open_sans.ttf');

        $candidates = match ($key) {
            'dm_sans', 'open_sans' => [$default],
            'mono' => [
                'C:\Windows\Fonts\consola.ttf',
                'C:\Windows\Fonts\cour.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSansMono.ttf',
                $default,
            ],
            'serif' => [
                'C:\Windows\Fonts\times.ttf',
                'C:\Windows\Fonts\timesbd.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSerif.ttf',
                $default,
            ],
            'handwriting' => [
                'C:\Windows\Fonts\comic.ttf',
                'C:\Windows\Fonts\comicbd.ttf',
                '/usr/share/fonts/truetype/msttcorefonts/Comic_Sans_MS.ttf',
                $default,
            ],
            default => [$default],
        };

        foreach ($candidates as $path) {
            if (is_string($path) && file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
