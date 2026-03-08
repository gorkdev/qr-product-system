<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductMediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_creates_with_main_image(): void
    {
        $file = UploadedFile::fake()->image('main.jpg', 800, 600);
        Livewire::test('product-create-form')
            ->set('name', 'Görselli Ürün')
            ->set('description', 'En az on karakterlik açıklama.')
            ->set('main_image', $file)
            ->call('save')
            ->assertHasNoErrors();

        $p = Product::where('name', 'Görselli Ürün')->first();
        $this->assertNotNull($p->images);
        $this->assertNotEmpty($p->images);
    }

    public function test_creates_with_youtube_videos(): void
    {
        $file = UploadedFile::fake()->image('x.jpg', 100, 100);
        Livewire::test('product-create-form')
            ->set('name', 'Videolu Ürün')
            ->set('description', 'En az on karakterlik açıklama.')
            ->set('main_image', $file)
            ->set('videos', ['https://youtu.be/dQw4w9WgXcQ', ''])
            ->call('save')
            ->assertHasNoErrors();

        $p = Product::where('name', 'Videolu Ürün')->first();
        $this->assertCount(1, $p->videos ?? []);
        $this->assertStringContainsString('youtu.be', $p->videos[0]);
    }

    public function test_empty_video_slots_ignored(): void
    {
        $file = UploadedFile::fake()->image('x.jpg', 100, 100);
        Livewire::test('product-create-form')
            ->set('name', 'Tek Video Ürün')
            ->set('description', 'En az on karakterlik açıklama.')
            ->set('main_image', $file)
            ->set('videos', ['', 'https://youtube.com/watch?v=abc', ''])
            ->call('save')
            ->assertHasNoErrors();

        $p = Product::where('name', 'Tek Video Ürün')->first();
        $this->assertCount(1, $p->videos ?? []);
    }
}
