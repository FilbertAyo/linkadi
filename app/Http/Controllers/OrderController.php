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
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'subscription_years' => ['required', 'integer', 'min:1', 'max:10'],
            'cards' => ['required_without:bulk', 'array'],
            'cards.*.profile_id' => ['required_with:cards', 'exists:profiles,id'],
            'cards.*.card_color' => ['required_with:cards', 'string', 'max:255'],
            'cards.*.requires_printing' => ['nullable'],
            'cards.*.printing_text' => ['nullable', 'string', 'max:255'],
            'bulk' => ['required_without:cards', 'array'],
            'bulk.profile_id' => ['required_with:bulk', 'exists:profiles,id'],
            'bulk.card_color' => ['required_with:bulk', 'string', 'max:255'],
            'bulk.requires_printing' => ['nullable'],
            'bulk.printing_text' => ['nullable', 'string', 'max:255'],
            'shipping_address' => ['required', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $package = Package::findOrFail($validated['package_id']);

        if (!$package->is_active) {
            return back()->with('error', 'This package is not available.');
        }

        // Validate minimum quantity for classic packages
        if ($package->type === 'classic' && $validated['quantity'] < 100) {
            return back()->withErrors(['quantity' => 'Minimum order quantity for Classic Business Cards is 100.'])->withInput();
        }

        // Determine if using bulk configuration
        $useBulkConfig = isset($validated['bulk']) && !empty($validated['bulk']);
        
        // Prepare card configurations
        $cardConfigs = [];
        $quantity = (int) $validated['quantity'];
        
        if ($useBulkConfig) {
            // Apply bulk config to all cards
            for ($i = 0; $i < $quantity; $i++) {
                $cardConfigs[] = [
                    'profile_id' => $validated['bulk']['profile_id'],
                    'card_color' => $validated['bulk']['card_color'],
                    'requires_printing' => isset($validated['bulk']['requires_printing']),
                    'printing_text' => $validated['bulk']['printing_text'] ?? null,
                ];
            }
        } else {
            // Use individual card configurations
            $cardConfigs = $validated['cards'] ?? [];
            
            // Convert requires_printing checkboxes
            foreach ($cardConfigs as $key => $config) {
                $cardConfigs[$key]['requires_printing'] = isset($config['requires_printing']);
            }
        }
        
        // Validate we have the right number of configurations
        if (count($cardConfigs) !== $quantity) {
            return back()->withErrors(['quantity' => 'Card configuration mismatch. Please try again.'])->withInput();
        }
        
        // Validate all profiles belong to user
        $profileIds = array_column($cardConfigs, 'profile_id');
        $userProfileCount = Auth::user()->profiles()->whereIn('id', $profileIds)->count();
        if ($userProfileCount !== count(array_unique($profileIds))) {
            return back()->withErrors(['cards' => 'One or more selected profiles do not belong to you.'])->withInput();
        }
        
        // Calculate total pricing
        $subscriptionYears = (int) $validated['subscription_years'];
        $subscriptionOption = collect($package->getSubscriptionOptions())->firstWhere('years', $subscriptionYears);
        
        if (!$subscriptionOption) {
            return back()->with('error', 'Invalid subscription duration selected.')->withInput();
        }
        
        // Calculate base price for all cards
        $baseSubscriptionPrice = $subscriptionOption['price'];
        $totalBasePrice = $baseSubscriptionPrice * $quantity;
        
        // Calculate printing fees
        $printingCount = collect($cardConfigs)->where('requires_printing', true)->count();
        $totalPrintingFee = $printingCount * (float) ($package->printing_fee ?? 0);
        
        // Calculate totals
        $totalPrice = $totalBasePrice + $totalPrintingFee;
        $totalDiscount = $subscriptionOption['savings'] * $quantity;
        
        // Create order
        $order = Order::create([
            'user_id' => Auth::id(),
            'package_id' => $package->id,
            'profile_id' => null, // Multiple profiles
            'quantity' => $quantity,
            'unit_price' => $baseSubscriptionPrice,
            'base_price' => $totalBasePrice,
            'subscription_price' => $totalBasePrice,
            'subscription_years' => $subscriptionYears,
            'subscription_discount' => $totalDiscount,
            'printing_fee' => $totalPrintingFee,
            'design_fee' => 0,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'payment_status' => 'pending',
            'requires_printing' => $printingCount > 0,
            'has_design' => true,
            'card_color' => null, // Multiple colors possible
            'pricing_breakdown' => [
                'subscription_option' => $subscriptionOption,
                'quantity' => $quantity,
                'base_per_card' => $baseSubscriptionPrice,
                'printing_count' => $printingCount,
                'printing_fee_per_card' => (float) ($package->printing_fee ?? 0),
                'total_printing' => $totalPrintingFee,
                'total_discount' => $totalDiscount,
                'card_configurations' => $cardConfigs,
            ],
            'shipping_address' => $validated['shipping_address'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Link all profiles to this order
        foreach ($cardConfigs as $config) {
            $profile = \App\Models\Profile::find($config['profile_id']);
            if ($profile && $profile->user_id === Auth::id()) {
                $profile->order_id = $order->id;
                $profile->package_id = $package->id;
                $profile->status = 'pending_payment';
                $profile->save();
            }
        }

        return redirect()->route('dashboard.orders.payment', $order)
            ->with('success', 'Order created successfully! Please complete payment to activate your cards.');
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
