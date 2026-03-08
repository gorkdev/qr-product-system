<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Public product view - herkes bu link ile sadece o ürünün bilgilerini görür.
     * share_token: 64 karakterlik tahmin edilemez benzersiz token
     */
    public function show(string $share_token)
    {
        $product = Product::where('share_token', $share_token)->firstOrFail();
        return view('product.show', ['product' => $product]);
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
