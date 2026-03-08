<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ayarlar sayfası işlemleri.
 */
class SettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_loads(): void
    {
        $response = $this->get(route('setting.index'));

        $response->assertStatus(200);
        $response->assertSee('Yönlendirme sitesi erişimi');
        $response->assertSee('Kaydet');
    }

    public function test_settings_page_saves_access_mode(): void
    {
        $response = $this->post(route('setting.update'), ['access_mode' => 'qr_only']);

        $response->assertRedirect(route('setting.index'));
        $this->assertEquals('qr_only', Setting::get('access_mode'));
    }

    public function test_settings_accepts_link_mode(): void
    {
        Setting::set('access_mode', 'qr_only');

        $this->post(route('setting.update'), ['access_mode' => 'link']);

        $this->assertEquals('link', Setting::get('access_mode'));
    }
}
