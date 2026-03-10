<?php

declare(strict_types=1);

use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Exceptions\DriverException;
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public ?string $productId = null;

    public ?Product $product = null;

    public bool $isEdit = false;

    #[Validate('required|min:3|max:255', message: [
        'required' => 'Ürün adı zorunludur.',
        'min' => 'Ürün adı en az 3 karakter olmalıdır.',
        'max' => 'Ürün adı en fazla 255 karakter olabilir.',
    ])]
    public string $name = '';

    #[Validate('required|min:10', message: [
        'required' => 'Açıklama zorunludur.',
        'min' => 'Açıklama en az 10 karakter olmalıdır.',
    ])]
    public string $description = '';

    public $main_image;

    public array $additional_images = [null];

    public array $videos = [''];

    public $pdf;

    public ?string $successMessage = null;

    public ?string $createdProductLink = null;

    public ?string $createdProductShareToken = null;

    public array $persistedErrors = [];

    public function mount(?string $productId = null): void
    {
        $this->productId = $productId;
        $this->isEdit = $productId !== null;

        if ($this->isEdit) {
            $this->product = Product::where('uuid', $productId)->firstOrFail();
            $this->name = $this->product->name;
            $this->description = $this->product->description ?? '';
            $this->videos = array_merge($this->product->videos ?? [], ['']);
        }
    }

    public function addVideo(): void
    {
        $this->videos[] = '';
    }

    public function removeVideo(int $index): void
    {
        unset($this->videos[$index]);
        $this->videos = array_values($this->videos);
    }

    public function addImage(): void
    {
        $this->additional_images[] = null;
    }

    public function removeImage(int $index): void
    {
        unset($this->additional_images[$index]);
        $this->additional_images = array_values($this->additional_images);
    }

    public function save(): void
    {
        $this->persistedErrors = [];

        $rules = [
            'name' => 'required|min:3|max:255',
            'description' => 'required|min:10',
        ];

        $messages = [
            'name.required' => 'Ürün adı zorunludur.',
            'name.min' => 'Ürün adı en az 3 karakter olmalıdır.',
            'name.max' => 'Ürün adı en fazla 255 karakter olabilir.',
            'description.required' => 'Açıklama zorunludur.',
            'description.min' => 'Açıklama en az 10 karakter olmalıdır.',
        ];

        if (! $this->isEdit) {
            $rules['main_image'] = 'required|image|mimes:jpeg,png,jpg,webp|max:2048';
            $messages['main_image.required'] = 'Kapak görseli zorunludur.';
            $messages['main_image.image'] = 'Sadece resim dosyası yükleyebilirsiniz.';
            $messages['main_image.mimes'] = 'Desteklenen formatlar: jpeg, png, jpg, webp.';
            $messages['main_image.max'] = 'Görsel en fazla 2MB olabilir.';
        } else {
            $rules['main_image'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048';
        }
        $rules['additional_images'] = 'nullable|array';
        $rules['additional_images.*'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048';
        $rules['pdf'] = 'nullable|file|mimes:pdf|max:10240';
        $messages['pdf.max'] = 'PDF en fazla 10MB olabilir.';

        // Validate videos (YouTube URLs)
        $videoUrls = array_filter($this->videos);
        foreach ($videoUrls as $url) {
            if (! preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+/', $url)) {
                $this->persistedErrors['videos'] = ['Geçerli bir YouTube URL\'si girin.'];
                session()->flash('product_form_errors', $this->persistedErrors);
                throw ValidationException::withMessages(['videos' => ['Geçerli bir YouTube URL\'si girin.']]);
            }
        }

        try {
            $this->validate($rules, $messages);
        } catch (ValidationException $e) {
            $messages = $e->validator->errors()->messages();
            $this->persistedErrors = $messages;
            session()->flash('product_form_errors', $messages);
            throw $e;
        }

        session()->forget('product_form_errors');
        $this->successMessage = null;

        $basePath = 'products';
        $images = [];
        $pdfPath = null;

        if ($this->isEdit && $this->product) {
            $product = $this->product;
            $basePath = $product->getStoragePath();
            $images = $product->images ?? [];
        } else {
            $product = Product::create([
                'name' => $this->name,
                'description' => $this->description,
                'images' => [],
                'videos' => [],
            ]);
            $basePath = $product->getStoragePath();
            $product->ensureQrCodeExists();
        }

        $imagesPath = $basePath . 'images/';
        $pdfDirPath = $basePath . 'pdf/';

        $hasImagesToStore = (bool) $this->main_image || ! empty(array_filter($this->additional_images));
        if ($hasImagesToStore) {
            Storage::disk('public')->makeDirectory($imagesPath);
            Storage::disk('public')->makeDirectory($imagesPath . 'thumbs/');
        }

        // Main image
        if ($this->main_image) {
            $newMainUrl = $this->storeImageWithThumb($this->main_image, $imagesPath);
            if ($this->isEdit && ! empty($images)) {
                $images[0] = $newMainUrl;
            } else {
                $images = array_merge([$newMainUrl], $images);
            }
        }

        // Additional images
        foreach ($this->additional_images as $img) {
            if (! $img) continue;
            $images[] = $this->storeImageWithThumb($img, $imagesPath);
        }

        // PDF (klasör sadece PDF varsa oluşturulur)
        if ($this->pdf) {
            Storage::disk('public')->makeDirectory($pdfDirPath);
            $pdfFilename = Str::random(40) . '.' . $this->pdf->getClientOriginalExtension();
            $this->pdf->storeAs($pdfDirPath, $pdfFilename, 'public');
            $pdfPath = Storage::url($pdfDirPath . $pdfFilename);
        } elseif ($this->isEdit && $product->pdf_path) {
            $pdfPath = $product->pdf_path;
        }

        $product->update([
            'name' => $this->name,
            'description' => $this->description,
            'images' => $images,
            'videos' => array_values(array_filter(array_map('trim', $videoUrls))),
            'pdf_path' => $pdfPath,
        ]);

        $product->ensureQrCodeExists();

        $this->successMessage = $this->isEdit ? 'Ürün başarıyla güncellendi!' : 'Ürün başarıyla kaydedildi!';
        $this->dispatch('toast', type: 'success', message: $this->successMessage);

        if (! $this->isEdit) {
            $gateUrl = url(route('product.gate', $product->share_token));
            $this->createdProductLink = $gateUrl;
            $this->createdProductShareToken = $product->share_token;
            $this->reset(['name', 'description', 'main_image', 'additional_images', 'videos', 'pdf']);
            $this->additional_images = [null];
            $this->videos = [''];
        } else {
            $this->reset(['main_image', 'pdf']);
            $this->additional_images = [null];
        }
    }

    private function storeImageWithThumb($file, string $imagesPath): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = Str::random(40) . '.' . $ext;
        $fullPath = Storage::disk('public')->path($imagesPath . $filename);
        $thumbPath = Storage::disk('public')->path($imagesPath . 'thumbs/' . $filename);

        try {
            $img = Image::read($file->getRealPath());
            $img->scaleDown(width: 1920)->save($fullPath, quality: 90);
            $img->cover(128, 128)->save($thumbPath, quality: 85);
        } catch (DriverException) {
            $file->storeAs($imagesPath, $filename, 'public');
        } catch (\Throwable) {
            $file->storeAs($imagesPath, $filename, 'public');
        }

        return Storage::url($imagesPath . $filename);
    }

    public function getFormErrors(): array
    {
        return $this->persistedErrors ?: session('product_form_errors', []);
    }
}; ?>

<div>
    <div class="card">
        <div class="card-body">
            @if($successMessage)
                <div class="alert alert-success">
                    <x-heroicon-o-check-circle class="alert-icon" />
                    {{ $successMessage }}
                </div>
            @endif

            @php
                $gateBase = $product ? url(route('product.gate', $product->share_token)) : null;
                $productLink = $createdProductLink ?? ($isEdit && $gateBase ? $gateBase : null);
            @endphp
            @if($productLink)
                <div class="product-link-box" x-data="{ copied: false }">
                    <label class="product-link-label">Ürün linki (herkes bu link ile ürün bilgisini görebilir)</label>
                    <div class="product-link-row">
                        <input type="text" class="form-input product-link-input" value="{{ $productLink }}" readonly>
                        <button type="button" class="btn btn-outline btn-sm product-link-copy"
                            data-copy="{{ $productLink }}"
                            @click="navigator.clipboard.writeText($el.dataset.copy).then(() => { copied = true; setTimeout(() => copied = false, 1500) })"
                            title="Kopyala">
                            <x-heroicon-o-link class="btn-icon" />
                            <span x-text="copied ? 'Kopyalandı' : 'Kopyala'">Kopyala</span>
                        </button>
                    </div>
                    @php $displayProduct = $product ?? ($createdProductShareToken ? \App\Models\Product::where('share_token', $createdProductShareToken)->first() : null); @endphp
                    @if($displayProduct && ($qrUrl = $displayProduct->getQrCodePath()))
                        <div class="product-qr-box">
                            <label class="product-link-label">QR Kod (taranarak linke gidilir)</label>
                            <div class="product-qr-row">
                                <img src="{{ $qrUrl }}" alt="QR Kod" class="product-qr-img" width="150" height="150">
                                <a href="{{ $qrUrl }}" download="qr-{{ $displayProduct->uuid }}.png" class="btn btn-outline btn-sm">İndir</a>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <form class="product-form" wire:key="product-form-{{ $isEdit ? 'edit-' . $productId : 'create' }}"
                  x-data="productFormUploader()"
                  @submit.prevent="uploadThenSave($event)">
                <div class="form-group">
                    <label for="name">Ürün Adı *</label>
                    <input type="text" id="name" wire:model.defer="name"
                        class="form-input @error('name') is-invalid @enderror"
                        placeholder="Örn: Kablosuz Kulaklık Pro">
                    @error('name')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="main_image">Kapak Görseli {{ $isEdit ? '' : '*' }}</label>
                    <label for="main_image" class="file-upload-area @error('main_image') is-invalid @enderror" x-data="{ fileName: '' }">
                        <input type="file" id="main_image" accept="image/jpeg,image/png,image/jpg,image/webp" class="file-input-hidden"
                            @change="fileName = $event.target.files[0]?.name || ''">
                        <div class="file-upload-content">
                            <x-heroicon-o-photo class="file-icon" />
                            <span class="file-text" x-text="fileName || 'Görsel yüklemek için tıklayın veya sürükleyin'">{{ $main_image ? $main_image->getClientOriginalName() : 'Görsel yüklemek için tıklayın veya sürükleyin' }}</span>
                            <span class="file-hint">JPEG, PNG, JPG, WEBP — Max 2MB (Kaydet'e tıklanınca yüklenir)</span>
                        </div>
                    </label>
                    @error('main_image')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                    @if($isEdit && $product && ($firstImg = ($product->images ?? [])[0] ?? null))
                        <p class="form-hint">Mevcut: <img src="{{ $product->thumbnailUrlFromFull($firstImg) }}" data-src-full="{{ $firstImg }}" alt="" class="thumb-preview" width="60" height="60" loading="lazy" onerror="this.onerror=null;this.src=this.dataset.srcFull"></p>
                    @endif
                </div>

                <div class="form-group">
                    <label>Ek Görseller</label>
                    @foreach($additional_images as $index => $img)
                        <div class="image-row" wire:key="additional-img-{{ $index }}" x-data="{ fileName: '' }">
                            <label class="file-upload-area file-upload-area-sm {{ !empty($this->getFormErrors()['additional_images']) ? 'is-invalid' : '' }}">
                                <input type="file" data-property="additional_images.{{ $index }}"
                                    accept="image/jpeg,image/png,image/jpg,image/webp" class="file-input-hidden"
                                    @change="fileName = $event.target.files[0]?.name || 'Görsel seç'">
                                <div class="file-upload-content">
                                    <x-heroicon-o-photo class="file-icon file-icon-sm" />
                                    <span class="file-text file-text-sm" x-text="fileName || 'Görsel seç'">{{ is_object($img) ? $img->getClientOriginalName() : 'Görsel seç' }}</span>
                                </div>
                            </label>
                            <button type="button" class="btn btn-outline btn-sm" wire:click.prevent="removeImage({{ $index }})"
                                @if(count($additional_images) <= 1) disabled @endif>
                                <x-heroicon-o-trash class="btn-icon" />
                            </button>
                        </div>
                    @endforeach
                    <button type="button" class="btn btn-outline btn-sm" wire:click.prevent="addImage">
                        <x-heroicon-o-plus class="btn-icon" /> Görsel Ekle
                    </button>
                    @error('additional_images')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Açıklama *</label>
                    <textarea id="description" wire:model.defer="description" rows="5"
                        class="form-input form-textarea @error('description') is-invalid @enderror"
                        placeholder="Ürünün detaylı açıklamasını yazın..."></textarea>
                    @error('description')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>YouTube Videoları</label>
                    @foreach($videos as $index => $video)
                        <div class="video-row" wire:key="video-{{ $index }}">
                            <input type="url" wire:model.defer="videos.{{ $index }}" class="form-input"
                                placeholder="https://www.youtube.com/watch?v=...">
                            <button type="button" class="btn btn-outline btn-sm" wire:click.prevent="removeVideo({{ $index }})"
                                @if(count($videos) <= 1) disabled @endif>
                                <x-heroicon-o-trash class="btn-icon" />
                            </button>
                        </div>
                    @endforeach
                    <button type="button" class="btn btn-outline btn-sm" wire:click.prevent="addVideo">
                        <x-heroicon-o-plus class="btn-icon" /> Video Ekle
                    </button>
                    @error('videos')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="pdf">PDF Dosyası</label>
                    <label for="pdf" class="file-upload-area @error('pdf') is-invalid @enderror" x-data="{ fileName: '' }">
                        <input type="file" id="pdf" accept=".pdf" class="file-input-hidden"
                            @change="fileName = $event.target.files[0]?.name || ''">
                        <div class="file-upload-content">
                            <x-heroicon-o-document-text class="file-icon" />
                            <span class="file-text" x-text="fileName || 'PDF yüklemek için tıklayın'">{{ $pdf ? $pdf->getClientOriginalName() : 'PDF yüklemek için tıklayın' }}</span>
                            <span class="file-hint">PDF — Max 10MB (Kaydet'e tıklanınca yüklenir)</span>
                        </div>
                    </label>
                    @error('pdf')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                    @if($isEdit && $product?->pdf_path)
                        <p class="form-hint">Mevcut PDF mevcut</p>
                    @endif
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"
                            :disabled="submitting"
                            wire:loading.attr="disabled"
                            wire:target="save">
                        <span x-show="!submitting">{{ $isEdit ? 'Güncelle' : 'Ürünü Kaydet' }}</span>
                        <span x-show="submitting" x-cloak style="display:none">Kaydediliyor...</span>
                    </button>
                    @if($isEdit)
                        <a href="{{ route('product.index') }}" class="btn btn-outline" wire:navigate>İptal</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

@script
<script>
Alpine.data('productFormUploader', () => ({
    submitting: false,

    async uploadThenSave(event) {
        if (this.submitting) return;
        this.submitting = true;

        try {
            const form = event.target;
            const mainInput = form.querySelector('#main_image');
            const pdfInput = form.querySelector('#pdf');
            const additionalInputs = form.querySelectorAll('input[data-property^="additional_images"]');

            const uploads = [];

            const uploadFile = (prop, file) => new Promise((resolve, reject) => {
                this.$wire.$upload(prop, file, resolve, reject, () => {});
            });

            if (mainInput?.files?.[0]) {
                uploads.push(uploadFile('main_image', mainInput.files[0]));
            }
            additionalInputs.forEach(input => {
                if (input.files?.[0]) {
                    uploads.push(uploadFile(input.dataset.property, input.files[0]));
                }
            });
            if (pdfInput?.files?.[0]) {
                uploads.push(uploadFile('pdf', pdfInput.files[0]));
            }

            if (uploads.length > 0) {
                await Promise.all(uploads).catch(() => {});
            }
            await this.$wire.save();
        } finally {
            this.submitting = false;
        }
    }
}));
</script>
@endscript
