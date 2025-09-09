<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        return view('carts.index', compact('cart'));
    }

    public function add(Request $request, Product $product)
    {
        $quantity = max(1, (int) $request->input('quantity', 1));
        $cart = session()->get('cart', []);

        $imageUrl = $product->mainImage ? Storage::url($product->mainImage->image_path) : asset('images/product-1.png');

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'image_url' => $imageUrl,
                'quantity' => $quantity,
            ];
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Added to cart');
    }

    public function update(Request $request, Product $product)
    {
        $quantity = (int) $request->input('quantity', 1);
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            if ($quantity <= 0) {
                unset($cart[$product->id]);
            } else {
                $cart[$product->id]['quantity'] = $quantity;
            }
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Cart updated');
    }

    public function remove(Product $product)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$product->id])) {
            unset($cart[$product->id]);
            session()->put('cart', $cart);
        }
        return back()->with('success', 'Item removed');
    }

    public function checkout()
    {
        $cart = session()->get('cart', []);
        return view('carts.checkout', compact('cart'));
    }
}


