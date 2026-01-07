<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\NfcCard;
use App\Models\Order;
use App\Models\Package;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CardController extends Controller
{
    /**
     * Show available NFC card packages.
     */
    public function packages()
    {
        $packages = Package::where('type', 'nfc_card')
            ->where('is_active', true)
            ->ordered()
            ->get();
            
        $userProfiles = Auth::user()->profiles;
        $draftProfiles = $userProfiles->where('status', 'draft');
        
        return view('client.cards.packages', compact('packages', 'userProfiles', 'draftProfiles'));
    }
    
    /**
     * Show checkout page for selected package.
     */
    public function checkout(Package $package)
    {
        // Validate package type
        if ($package->type !== 'nfc_card' || !$package->is_active) {
            return redirect()->route('dashboard.cards.packages')
                ->with('error', 'This package is not available.');
        }
        
        // Get user's profiles that can be linked
        $availableProfiles = Auth::user()->profiles()
            ->whereIn('status', ['draft', 'pending_payment'])
            ->get();
        
        // If no profiles, redirect to create one
        if ($availableProfiles->isEmpty()) {
            return redirect()->route('profile.builder.create')
                ->with('info', 'Please create a profile first before ordering an NFC card.');
        }
        
        return view('client.cards.checkout', compact('package', 'availableProfiles'));
    }
    
    /**
     * Process bulk NFC card order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'quantity' => 'required|integer|min:1|max:10',
            'subscription_years' => 'required|integer|min:1|max:10',
            'cards' => 'required_without:bulk|array|max:10',
            'cards.*.profile_id' => 'required_with:cards|exists:profiles,id',
            'cards.*.card_color' => 'required_with:cards|string|max:50',
            'cards.*.requires_printing' => 'nullable',
            'cards.*.printing_text' => 'nullable|string|max:255',
            'bulk' => 'required_without:cards|array',
            'bulk.profile_id' => 'required_with:bulk|exists:profiles,id',
            'bulk.card_color' => 'required_with:bulk|string|max:50',
            'bulk.requires_printing' => 'nullable',
            'bulk.printing_text' => 'nullable|string|max:255',
            'shipping_address' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $package = Package::findOrFail($validated['package_id']);
        
        // Validate package type
        if ($package->type !== 'nfc_card' || !$package->is_active) {
            return back()->with('error', 'This package is not available.');
        }
        
        // Determine if using bulk configuration
        $useBulkConfig = isset($validated['bulk']) && !empty($validated['bulk']);
        $quantity = (int) $validated['quantity'];
        
        // Prepare card configurations
        $cardConfigs = [];
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
            foreach ($validated['cards'] as $config) {
                $cardConfigs[] = [
                    'profile_id' => $config['profile_id'],
                    'card_color' => $config['card_color'],
                    'requires_printing' => isset($config['requires_printing']),
                    'printing_text' => $config['printing_text'] ?? null,
                ];
            }
        }
        
        // Validate we have the right number of configurations
        if (count($cardConfigs) !== $quantity) {
            return back()->withErrors(['quantity' => 'Card configuration mismatch.'])->withInput();
        }
        
        // Validate all profiles belong to user
        $profileIds = array_column($cardConfigs, 'profile_id');
        $userProfileCount = Auth::user()->profiles()->whereIn('id', $profileIds)->count();
        if ($userProfileCount !== count(array_unique($profileIds))) {
            return back()->withErrors(['cards' => 'One or more profiles do not belong to you.'])->withInput();
        }
        
        // Get subscription pricing
        $subscriptionYears = (int) $validated['subscription_years'];
        $subscriptionOption = collect($package->getSubscriptionOptions())->firstWhere('years', $subscriptionYears);
        
        if (!$subscriptionOption) {
            return back()->with('error', 'Invalid subscription duration.')->withInput();
        }
        
        // Calculate pricing
        $pricePerCard = $subscriptionOption['price'];
        $totalBasePrice = $pricePerCard * $quantity;
        $printingCount = collect($cardConfigs)->where('requires_printing', true)->count();
        $totalPrintingFee = $printingCount * (float) ($package->printing_fee ?? 0);
        $totalPrice = $totalBasePrice + $totalPrintingFee;
        $totalDiscount = $subscriptionOption['savings'] * $quantity;
        
        try {
            $order = DB::transaction(function () use ($validated, $package, $cardConfigs, $totalPrice, $totalBasePrice, $totalPrintingFee, $totalDiscount, $subscriptionYears, $pricePerCard, $quantity) {
                // Create main order
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'package_id' => $package->id,
                    'quantity' => $quantity,
                    'unit_price' => $pricePerCard,
                    'base_price' => $totalBasePrice,
                    'subscription_price' => $totalBasePrice,
                    'subscription_years' => $subscriptionYears,
                    'subscription_discount' => $totalDiscount,
                    'printing_fee' => $totalPrintingFee,
                    'design_fee' => 0,
                    'total_price' => $totalPrice,
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'shipping_address' => $validated['shipping_address'],
                    'notes' => $validated['notes'] ?? null,
                    'pricing_breakdown' => [
                        'subscription_years' => $subscriptionYears,
                        'price_per_card' => $pricePerCard,
                        'printing_fee_per_card' => (float) ($package->printing_fee ?? 0),
                        'card_configurations' => $cardConfigs,
                    ],
                ]);
                
                // Create NFC card records for each card
                foreach ($cardConfigs as $index => $cardData) {
                    $nfcCard = NfcCard::create([
                        'user_id' => Auth::id(),
                        'profile_id' => $cardData['profile_id'],
                        'order_id' => $order->id,
                        'package_id' => $package->id,
                        'card_number' => NfcCard::generateCardNumber(),
                        'card_color' => $cardData['card_color'],
                        'requires_printing' => $cardData['requires_printing'],
                        'printing_text' => $cardData['printing_text'] ? ['text' => $cardData['printing_text']] : null,
                        'status' => 'pending_production',
                        'expires_at' => now()->addDays(($package->subscription_duration_days ?? 365) * $subscriptionYears),
                    ]);
                    
                    // Update profile to pending_payment
                    $profile = Profile::find($cardData['profile_id']);
                    if ($profile && $profile->user_id === Auth::id()) {
                        $profile->status = 'pending_payment';
                        $profile->order_id = $order->id;
                        $profile->package_id = $package->id;
                        $profile->save();
                    }
                }
                
                Log::info('NFC card order created', [
                    'user_id' => Auth::id(),
                    'order_id' => $order->id,
                    'quantity' => $quantity,
                    'subscription_years' => $subscriptionYears,
                    'total' => $totalPrice,
                ]);
                
                return $order;
            });
            
            // Redirect to payment page
            return redirect()->route('dashboard.orders.payment', $order)
                ->with('success', 'Order created successfully! Please complete payment to activate your profiles.');
                
        } catch (\Exception $e) {
            Log::error('Failed to create NFC card order', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->withErrors(['error' => 'Failed to create order: ' . $e->getMessage()])->withInput();
        }
    }
}
