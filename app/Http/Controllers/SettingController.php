<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Setting;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Label\Font\Font;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Label\Margin\Margin;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SettingController extends Controller
{
    public function index(): View
    {
        $default = [
            'foreground' => '#111827',
            'background' => '#ffffff',
            'label_text' => '',
            'label_align' => 'center',
            'label_color' => '#111827',
            'label_font' => 'dm_sans',
            'label_font_size' => 16,
            'label_position' => 'bottom',
            'label_margin_top' => 8,
            'label_margin_bottom' => 8,
        ];

        $stored = json_decode((string) Setting::get('qr_style', ''), true) ?: [];
        $qr = array_merge($default, is_array($stored) ? $stored : []);

        return view('admin.settings', ['qr' => $qr]);
    }

    /**
     * QR kod ayarlarını günceller.
     */
    public function update(Request $request): RedirectResponse
    {
        if ($request->has('reset')) {
            $default = [
                'foreground' => '#111827',
                'background' => '#ffffff',
                'label_text' => '',
                'label_align' => 'center',
                'label_color' => '#111827',
                'label_font' => 'DM Sans',
            ];
            Setting::set('qr_style', json_encode($default));

            return redirect()
                ->route('setting.index')
                ->with('success', 'QR kod ayarları varsayılana döndürüldü.');
        }

        $data = $request->validate([
            'qr_foreground' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'qr_background' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'label_text' => ['nullable', 'string', 'max:80'],
            'label_align' => ['required', 'in:left,center,right'],
            'label_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'label_font' => ['required', 'in:dm_sans,open_sans,mono,serif,handwriting'],
            'label_font_size' => ['required', 'integer', 'min:10', 'max:28'],
            'label_position' => ['required', 'in:top,bottom'],
            'label_margin_top' => ['required', 'integer', 'min:0', 'max:40'],
            'label_margin_bottom' => ['required', 'integer', 'min:0', 'max:40'],
        ]);

        $config = [
            'foreground' => $data['qr_foreground'],
            'background' => $data['qr_background'],
            'label_text' => $data['label_text'] ?? '',
            'label_align' => $data['label_align'],
            'label_color' => $data['label_color'],
            'label_font' => $data['label_font'] ?? 'dm_sans',
            'label_font_size' => $data['label_font_size'] ?? 16,
            'label_position' => $data['label_position'] ?? 'bottom',
            'label_margin_top' => $data['label_margin_top'] ?? 8,
            'label_margin_bottom' => $data['label_margin_bottom'] ?? 8,
        ];

        Setting::set('qr_style', json_encode($config));

        return redirect()
            ->route('setting.index')
            ->with('success', 'QR kod ayarları kaydedildi.');
    }

    public function preview(Request $request): Response
    {
        $default = [
            'foreground' => '#111827',
            'background' => '#ffffff',
            'label_text' => '',
            'label_align' => 'center',
            'label_color' => '#111827',
            'label_font' => 'dm_sans',
            'label_font_size' => 16,
            'label_position' => 'bottom',
            'label_margin_top' => 8,
            'label_margin_bottom' => 8,
        ];

        $stored = json_decode((string) Setting::get('qr_style', ''), true) ?: [];
        $config = array_merge($default, is_array($stored) ? $stored : []);

        // Query parametreleri ile anlık önizleme
        $config['foreground'] = $request->query('fg', $config['foreground']);
        $config['background'] = $request->query('bg', $config['background']);
        $config['label_text'] = $request->query('text', $config['label_text']);
        $config['label_align'] = $request->query('align', $config['label_align']);
        $config['label_color'] = $request->query('lc', $config['label_color']);
        $config['label_font'] = $request->query('font', $config['label_font']);
        $config['label_font_size'] = (int) $request->query('fs', $config['label_font_size']);
        $config['label_position'] = $request->query('pos', $config['label_position']);
        $config['label_margin_top'] = (int) $request->query('mt', $config['label_margin_top']);
        $config['label_margin_bottom'] = (int) $request->query('mb', $config['label_margin_bottom']);

        $fg = $this->hexToColor($config['foreground'], new Color(17, 24, 39));
        $bg = $this->hexToColor($config['background'], new Color(255, 255, 255));

        // Temel QR kodu (labelsız) üret
        $qrCode = new QrCode(
            data: url('/'),
            size: 220,
            margin: 8,
            foregroundColor: $fg,
            backgroundColor: $bg
        );

        $writer = new PngWriter();
        $baseResult = $writer->write($qrCode);

        $text = trim((string) $config['label_text']);
        if ($text === '') {
            return response($baseResult->getString(), 200, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            ]);
        }

        $fontSize = max(10, min(28, (int) ($config['label_font_size'] ?? 16)));
        $fontKey = (string) ($config['label_font'] ?? 'dm_sans');
        $fontPath = $this->resolveFontPath($fontKey);

        if ($fontPath === null || !file_exists($fontPath)) {
            return response($baseResult->getString(), 200, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            ]);
        }

        $qrImage = imagecreatefromstring($baseResult->getString());
        if (!$qrImage) {
            return response($baseResult->getString(), 200, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            ]);
        }

        $qrWidth = imagesx($qrImage);
        $qrHeight = imagesy($qrImage);

        // Etiket ölçüleri
        $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
        $textWidth = abs($bbox[2] - $bbox[0]);
        $textHeight = abs($bbox[7] - $bbox[1]);

        $paddingX = 12;
        $marginTop = max(0, min(40, (int) ($config['label_margin_top'] ?? 8)));
        $marginBottom = max(0, min(40, (int) ($config['label_margin_bottom'] ?? 8)));

        $canvasWidth = max($qrWidth, $textWidth + 2 * $paddingX);
        $canvasHeight = $qrHeight + $textHeight + $marginTop + $marginBottom;

        $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);

        $bgColor = imagecolorallocate($canvas, $bg->getRed(), $bg->getGreen(), $bg->getBlue());
        imagefill($canvas, 0, 0, $bgColor);

        $labelColor = $this->hexToColor($config['label_color'], new Color(17, 24, 39));
        $textColor = imagecolorallocate($canvas, $labelColor->getRed(), $labelColor->getGreen(), $labelColor->getBlue());

        $alignment = LabelAlignment::tryFrom($config['label_align']) ?? LabelAlignment::Center;
        $position = $config['label_position'] ?? 'bottom';

        // QR'ı ve etiketi konuma göre yerleştir
        if ($position === 'top') {
            $textBaselineY = $marginTop + $textHeight;
            $qrDstY = $marginTop + $textHeight + $marginBottom;
        } else {
            $qrDstY = 0;
            $textBaselineY = $qrHeight + $marginTop + $textHeight;
        }

        $qrDstX = (int) (($canvasWidth - $qrWidth) / 2);
        imagecopy($canvas, $qrImage, $qrDstX, $qrDstY, 0, 0, $qrWidth, $qrHeight);

        // Etiketin X konumu
        if ($alignment === LabelAlignment::Left) {
            $textX = $paddingX;
        } elseif ($alignment === LabelAlignment::Right) {
            $textX = $canvasWidth - $paddingX - $textWidth;
        } else {
            $textX = (int) (($canvasWidth - $textWidth) / 2);
        }

        imagettftext($canvas, $fontSize, 0, $textX, $textBaselineY, $textColor, $fontPath, $text);

        ob_start();
        imagepng($canvas);
        $pngData = (string) ob_get_clean();

        imagedestroy($canvas);
        imagedestroy($qrImage);

        return response($pngData, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
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
     * Seçilen kısa ada göre font dosya yolunu döner.
     * Bulamazsa null döner (çağıran taraf fallback uygular).
     */
    private function resolveFontPath(string $key): ?string
    {
        $key = strtolower($key);

        // Proje ile gelen varsayılan font (DM Sans / Open Sans benzeri)
        $default = base_path('vendor/endroid/qr-code/assets/open_sans.ttf');

        // Windows ve Linux için yaygın font yolları
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
                '/usr/share/fonts/truetype/fonts-japanese-gothic.ttf',
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
