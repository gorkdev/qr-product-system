<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $accessMode = Setting::get('access_mode', 'link');
        return view('admin.settings', ['accessMode' => $accessMode]);
    }

    /**
     * access_mode: 'qr_only' | 'link'
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate(['access_mode' => 'required|in:qr_only,link']);

        Setting::set('access_mode', $request->input('access_mode'));

        return redirect()->route('setting.index')->with('success', 'Ayarlar kaydedildi.');
    }
}
