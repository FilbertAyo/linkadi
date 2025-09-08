<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index()
    {
        $products = Product::with(['mainImage', 'images'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        return view('admin.products.create');
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'is_active' => 'nullable', // <--- changed
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'main_image' => 'nullable|integer'
        ]);

        // Create product
        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'stock_quantity' => $request->stock_quantity,
            'description' => $request->description,
            'category' => $request->category,
            'sku' => $request->sku,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            $mainImageIndex = $request->input('main_image', 0);

            foreach ($request->file('images') as $index => $image) {
                $imageName = time() . '_' . $index . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('products', $imageName, 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                    'is_main' => $index == $mainImageIndex ? 1 : 0,
                ]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        $product->load(['images', 'mainImage']);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit(Product $product)
    {
        $product->load('images');
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'is_active' => 'nullable',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'main_image' => 'nullable|integer'
        ]);

        // Update product
        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'stock_quantity' => $request->stock_quantity,
            'description' => $request->description,
            'category' => $request->category,
            'sku' => $request->sku,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        // Handle new image uploads
        if ($request->hasFile('images')) {
            $mainImageIndex = $request->input('main_image', 0);

            foreach ($request->file('images') as $index => $image) {
                $imageName = time() . '_' . $index . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('products', $imageName, 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                    'is_main' => $index == $mainImageIndex ? 1 : 0,
                ]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
        // Delete associated images from storage
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);

        $status = $product->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Product {$status} successfully!");
    }

    /**
     * Delete specific product image
     */
    public function deleteImage(ProductImage $image)
    {
        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return redirect()->back()->with('success', 'Image deleted successfully!');
    }
}
