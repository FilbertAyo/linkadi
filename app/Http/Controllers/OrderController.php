<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     */
    public function index()
    {
        $orders = Auth::user()->orders()
            ->with('package')
            ->latest()
            ->paginate(15);

        return view('orders.index', compact('orders'));
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'shipping_address' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $package = Package::findOrFail($validated['package_id']);

        if (!$package->is_active) {
            return back()->with('error', 'This package is not available.');
        }

        // Calculate price
        $price = $package->getPriceForQuantity($validated['quantity']);
        
        if ($price === null) {
            return back()->with('error', 'Unable to calculate price for this quantity. Please contact support.');
        }

        // For classic packages, get the unit price from the tier
        if ($package->type === 'classic') {
            $tier = $package->activePricingTiers()
                ->where('min_quantity', '<=', $validated['quantity'])
                ->where(function ($query) use ($validated) {
                    $query->whereNull('max_quantity')
                        ->orWhere('max_quantity', '>=', $validated['quantity']);
                })
                ->first();
            
            $unitPrice = $tier ? $tier->price_per_unit : 0;
        } else {
            $unitPrice = $package->base_price;
        }

        $order = Order::create([
            'user_id' => Auth::id(),
            'package_id' => $package->id,
            'quantity' => $validated['quantity'],
            'unit_price' => $unitPrice,
            'total_price' => $price,
            'status' => 'pending',
            'shipping_address' => $validated['shipping_address'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order placed successfully!');
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        // Ensure user can only view their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load('package', 'package.pricingTiers');

        return view('orders.show', compact('order'));
    }
}
