<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_name_min_3_characters(): void
    {
        $file = UploadedFile::fake()->image('x.jpg', 100, 100);
        Livewire::test('product-create-form')
            ->set('name', 'ab')
            ->set('description', 'En az on karakterlik açıklama.')
            ->set('main_image', $file)
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_name_max_255_characters(): void
    {
        $file = UploadedFile::fake()->image('x.jpg', 100, 100);
        Livewire::test('product-create-form')
            ->set('name', str_repeat('a', 256))
            ->set('description', 'En az on karakterlik açıklama.')
            ->set('main_image', $file)
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_name_exactly_3_characters_ok(): void
    {
        $file = UploadedFile::fake()->image('x.jpg', 100, 100);
        Livewire::test('product-create-form')
            ->set('name', 'abc')
            ->set('description', 'En az on karakterlik açıklama.')
            ->set('main_image', $file)
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_description_min_10_characters(): void
    {
        $file = UploadedFile::fake()->image('x.jpg', 100, 100);
        Livewire::test('product-create-form')
            ->set('name', 'Yeterli İsim')
            ->set('description', 'kısa')
            ->set('main_image', $file)
            ->call('save')
            ->assertHasErrors(['description']);
    }

    public function test_description_exactly_10_characters_ok(): void
    {
        $file = UploadedFile::fake()->image('x.jpg', 100, 100);
        Livewire::test('product-create-form')
            ->set('name', 'Geçerli Ürün')
            ->set('description', '0123456789')
            ->set('main_image', $file)
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_invalid_youtube_url_rejected(): void
    {
        $file = UploadedFile::fake()->image('x.jpg', 100, 100);
        Livewire::test('product-create-form')
            ->set('name', 'Video Ürün')
            ->set('description', 'En az on karakterlik açıklama.')
            ->set('main_image', $file)
            ->set('videos', ['https://vimeo.com/123'])
            ->call('save')
            ->assertHasErrors(['videos']);
    }

    public function test_valid_youtube_url_accepted(): void
    {
        $file = UploadedFile::fake()->image('x.jpg', 100, 100);
        Livewire::test('product-create-form')
            ->set('name', 'Video Ürün')
            ->set('description', 'En az on karakterlik açıklama.')
            ->set('main_image', $file)
            ->set('videos', ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'])
            ->call('save')
            ->assertHasNoErrors();
    }
}
