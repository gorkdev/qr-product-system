<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_invalid_access_mode(): void
    {
        $response = $this->post(route('setting.update'), ['access_mode' => 'invalid']);
        $response->assertSessionHasErrors('access_mode');
    }

    public function test_requires_access_mode(): void
    {
        $response = $this->post(route('setting.update'), []);
        $response->assertSessionHasErrors('access_mode');
    }

    public function test_accepts_qr_only(): void
    {
        $this->post(route('setting.update'), ['access_mode' => 'qr_only']);
        $this->assertEquals('qr_only', Setting::get('access_mode'));
    }

    public function test_accepts_link(): void
    {
        Setting::set('access_mode', 'qr_only');
        $this->post(route('setting.update'), ['access_mode' => 'link']);
        $this->assertEquals('link', Setting::get('access_mode'));
    }
}
