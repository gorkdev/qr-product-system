<?php

declare(strict_types=1);

namespace Tests\Feature\Product;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductEditUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_update_with_new_image(): void
    {
        $p = Product::factory()->create(['name' => 'Eski']);
        $file = UploadedFile::fake()->image('yeni.jpg', 200, 200);

        Livewire::test('product-create-form', ['productId' => $p->uuid])
            ->set('name', 'Yeni')
            ->set('description', 'En az on karakterlik açıklama.')
            ->set('main_image', $file)
            ->call('save')
            ->assertHasNoErrors();

        $p->refresh();
        $this->assertNotEmpty($p->images);
    }

    public function test_update_adds_youtube_video(): void
    {
        $p = Product::factory()->create(['videos' => []]);

        Livewire::test('product-create-form', ['productId' => $p->uuid])
            ->set('name', $p->name)
            ->set('description', $p->description ?? 'En az on karakterlik.')
            ->set('videos', ['https://youtube.com/watch?v=abc123'])
            ->call('save')
            ->assertHasNoErrors();

        $p->refresh();
        $this->assertCount(1, $p->videos);
    }

    public function test_update_clears_videos_when_empty(): void
    {
        $p = Product::factory()->create(['videos' => ['https://youtube.com/watch?v=x']]);

        Livewire::test('product-create-form', ['productId' => $p->uuid])
            ->set('name', $p->name)
            ->set('description', $p->description ?? 'En az on karakterlik.')
            ->set('videos', ['', ''])
            ->call('save')
            ->assertHasNoErrors();

        $p->refresh();
        $this->assertEmpty($p->videos);
    }
}
