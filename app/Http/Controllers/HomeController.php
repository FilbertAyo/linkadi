<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class HomeController extends Controller
{
    public function welcome(){
        $products = Product::with(['mainImage'])
            ->where('is_active', 1)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return view('welcome', compact('products'));
    }

    public function shop()
    {
        $products = Product::with(['mainImage'])
            ->where('is_active', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(12);
        return view('shop', compact('products'));
    }

    public function product(Product $product)
    {
        $product->load(['images', 'mainImage']);
        return view('product.show', compact('product'));
    }

    public function contact()
    {
        return view('contact');
    }

}
