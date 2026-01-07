<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Profile;
use App\Models\Order;
use App\Models\NfcCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfilePackageController extends Controller
{
    /**
     * Show package selection for a profile.
     */
    public function selectPackage(Profile $profile)
    {
        // Ensure user owns the profile
        if ($profile->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this profile.');
        }
        
        // Get active NFC card packages
        $packages = Package::where('type', 'nfc_card')
            ->where('is_active', true)
            ->ordered()
            ->get();
        
        return view('client.profiles.select-package', compact('profile', 'packages'));
    }
    
    /**
     * Show order form for profile with selected package.
     */
    public function orderForm(Profile $profile, Package $package)
    {
        // Ensure user owns the profile
        if ($profile->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this profile.');
        }
        
        // Validate package
        if ($package->type !== 'nfc_card' || !$package->is_active) {
            return redirect()->route('profile.select-package', $profile)
                ->with('error', 'This package is not available.');
        }
        
        return view('client.profiles.order-form', compact('profile', 'package'));
    }
    
    /**
     * Create order for profile with selected package.
     */
    public function createOrder(Profile $profile, Request $request)
    {
        // Ensure user owns the profile
        if ($profile->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this profile.');
        }
        
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'subscription_years' => 'required|integer|min:1|max:10',
            'card_color' => 'required|string|max:50',
            'requires_printing' => 'nullable|boolean',
            'printing_text' => 'nullable|string|max:255',
            'shipping_address' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $package = Package::findOrFail($validated['package_id']);
        
        // Validate package
        if ($package->type !== 'nfc_card' || !$package->is_active) {
            return back()->with('error', 'This package is not available.');
        }
        
        // Get subscription pricing
        $subscriptionYears = (int) $validated['subscription_years'];
        $subscriptionOption = collect($package->getSubscriptionOptions())->firstWhere('years', $subscriptionYears);
        
        if (!$subscriptionOption) {
            return back()->with('error', 'Invalid subscription duration.')->withInput();
        }
        
        // Calculate pricing
        $pricePerCard = $subscriptionOption['price'];
        $requiresPrinting = isset($validated['requires_printing']) && $validated['requires_printing'];
        $printingFee = $requiresPrinting ? (float) ($package->printing_fee ?? 0) : 0;
        $totalPrice = $pricePerCard + $printingFee;
        
        try {
            $order = DB::transaction(function () use ($validated, $package, $profile, $totalPrice, $pricePerCard, $printingFee, $subscriptionYears, $requiresPrinting, $subscriptionOption) {
                // Create order
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'package_id' => $package->id,
                    'quantity' => 1,
                    'unit_price' => $pricePerCard,
                    'base_price' => $pricePerCard,
                    'subscription_price' => $pricePerCard,
                    'subscription_years' => $subscriptionYears,
                    'subscription_discount' => $subscriptionOption['savings'] ?? 0,
                    'printing_fee' => $printingFee,
                    'design_fee' => 0,
                    'total_price' => $totalPrice,
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'shipping_address' => $validated['shipping_address'],
                    'notes' => $validated['notes'] ?? null,
                    'pricing_breakdown' => [
                        'subscription_years' => $subscriptionYears,
                        'price_per_card' => $pricePerCard,
                        'printing_fee_per_card' => $printingFee,
                        'card_configurations' => [[
                            'profile_id' => $profile->id,
                            'card_color' => $validated['card_color'],
                            'requires_printing' => $requiresPrinting,
                            'printing_text' => $validated['printing_text'] ?? null,
                        ]],
                    ],
                ]);
                
                // Create NFC card record
                NfcCard::create([
                    'user_id' => Auth::id(),
                    'profile_id' => $profile->id,
                    'order_id' => $order->id,
                    'package_id' => $package->id,
                    'card_number' => NfcCard::generateCardNumber(),
                    'card_color' => $validated['card_color'],
                    'requires_printing' => $requiresPrinting,
                    'printing_text' => $requiresPrinting && $validated['printing_text'] ? ['text' => $validated['printing_text']] : null,
                    'status' => 'pending_production',
                    'expires_at' => now()->addDays(($package->subscription_duration_days ?? 365) * $subscriptionYears),
                ]);
                
                // Update profile
                $profile->update([
                    'status' => 'pending_payment',
                    'order_id' => $order->id,
                    'package_id' => $package->id,
                ]);
                
                Log::info('Profile package order created', [
                    'user_id' => Auth::id(),
                    'profile_id' => $profile->id,
                    'order_id' => $order->id,
                    'package_id' => $package->id,
                    'subscription_years' => $subscriptionYears,
                    'total' => $totalPrice,
                ]);
                
                return $order;
            });
            
            // Redirect to payment page
            return redirect()->route('dashboard.orders.payment', $order)
                ->with('success', 'Order created successfully! Please complete payment to activate your profile.');
                
        } catch (\Exception $e) {
            Log::error('Failed to create profile package order', [
                'user_id' => Auth::id(),
                'profile_id' => $profile->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->withErrors(['error' => 'Failed to create order: ' . $e->getMessage()])->withInput();
        }
    }
}
