<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     */
    public function index(Request $request)
    {
        $query = Auth::user()->orders()
            ->with(['package', 'nfcCards.profile'])
            ->latest();
        
        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Filter by payment status if provided
        if ($request->has('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }
        
        $orders = $query->paginate(15);
        
        // Get counts for filter badges
        $statusCounts = [
            'all' => Auth::user()->orders()->count(),
            'pending' => Auth::user()->orders()->where('status', 'pending')->count(),
            'processing' => Auth::user()->orders()->where('status', 'processing')->count(),
            'shipped' => Auth::user()->orders()->where('status', 'shipped')->count(),
            'delivered' => Auth::user()->orders()->where('status', 'delivered')->count(),
        ];
        
        $paymentStatusCounts = [
            'all' => Auth::user()->orders()->count(),
            'pending' => Auth::user()->orders()->where('payment_status', 'pending')->count(),
            'paid' => Auth::user()->orders()->where('payment_status', 'paid')->count(),
        ];
        
        return view('client.orders.index', compact('orders', 'statusCounts', 'paymentStatusCounts'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        // Ensure user can only view their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load(['package', 'nfcCards.profile']);

        return view('client.orders.show', compact('order'));
    }
    
    /**
     * Show payment page for an order.
     */
    public function payment(Order $order)
    {
        // Ensure user can only pay for their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this order.');
        }
        
        // Check if order is already paid
        if ($order->payment_status === 'paid') {
            return redirect()->route('dashboard.orders.show', $order)
                ->with('info', 'This order has already been paid.');
        }
        
        // Check if order is cancelled
        if ($order->status === 'cancelled') {
            return redirect()->route('dashboard.orders.show', $order)
                ->with('error', 'This order has been cancelled.');
        }
        
        $order->load(['package', 'nfcCards.profile']);
        
        return view('client.orders.payment', compact('order'));
    }
    
    /**
     * Process payment for an order.
     */
    public function processPayment(Order $order, Request $request)
    {
        // Ensure user can only pay for their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this order.');
        }
        
        // Validate payment request
        $validated = $request->validate([
            'payment_method' => ['required', 'in:mpesa,tigo_pesa,airtel_money,halopesa'],
            'phone_number' => ['required', 'regex:/^0[67]\d{8}$/'],
        ]);
        
        // Check if order is already paid
        if ($order->payment_status === 'paid') {
            return redirect()->route('dashboard.orders.show', $order)
                ->with('info', 'This order has already been paid.');
        }
        
        // For now, simulate successful payment (mock data)
        // In production, this would integrate with actual payment gateway
        
        // Update order with payment info
        $order->update([
            'payment_method' => $validated['payment_method'],
            'payment_reference' => 'TXN' . strtoupper($validated['payment_method']) . time(),
            'payment_status' => 'paid',
            'paid_at' => now(),
            'status' => 'processing',
        ]);
        
        // Handle renewal orders vs new card orders
        $subscriptionYears = $order->subscription_years ?? 1;
        $durationDays = ($order->package->subscription_duration_days ?? 365) * $subscriptionYears;
        
        // Check if this is a renewal order (has profile_id directly)
        if ($order->profile_id) {
            // This is a subscription renewal
            $profile = \App\Models\Profile::find($order->profile_id);
            if ($profile && $profile->user_id === Auth::id()) {
                // Calculate new expiration date (extend from current expiration or now if expired)
                $startDate = $profile->expires_at && $profile->expires_at->isFuture()
                    ? $profile->expires_at
                    : now();
                
                $profile->update([
                    'status' => 'published',
                    'published_at' => $profile->published_at ?? now(),
                    'expires_at' => $startDate->copy()->addDays($durationDays),
                    'order_id' => $order->id,
                ]);
                
                // Log renewal
                \Illuminate\Support\Facades\Log::info('Subscription renewal payment processed', [
                    'order_id' => $order->id,
                    'profile_id' => $profile->id,
                    'user_id' => Auth::id(),
                    'years' => $subscriptionYears,
                    'new_expires_at' => $profile->expires_at,
                ]);
            }
        } else {
            // This is a new card order
            $breakdown = $order->pricing_breakdown ?? [];
            $cardConfigs = $breakdown['card_configurations'] ?? [];
            
            foreach ($cardConfigs as $config) {
                $profile = \App\Models\Profile::find($config['profile_id']);
                if ($profile && $profile->user_id === Auth::id()) {
                    $profile->update([
                        'status' => 'published',
                        'published_at' => $profile->published_at ?? now(),
                        'expires_at' => now()->addDays($durationDays),
                    ]);
                }
            }
        }
        
        // Log the payment
        \Illuminate\Support\Facades\Log::info('Payment processed', [
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'method' => $validated['payment_method'],
            'phone' => $validated['phone_number'],
            'amount' => $order->total_price,
            'is_renewal' => $order->profile_id !== null,
        ]);
        
        // Different success message for renewals
        $successMessage = $order->profile_id 
            ? "Payment successful! Your subscription has been renewed for {$subscriptionYears} " . \Illuminate\Support\Str::plural('year', $subscriptionYears) . "."
            : 'Payment successful! Your order is now being processed. Cards will be delivered in 5-7 business days.';
        
        return redirect()->route('dashboard.orders.show', $order)
            ->with('success', $successMessage);
    }
    
    /**
     * Show pending payment orders (cart/invoices).
     */
    public function pending()
    {
        $orders = Auth::user()->orders()
            ->where('payment_status', 'pending')
            ->with(['package'])
            ->latest()
            ->paginate(15);
        
        return view('client.orders.pending', compact('orders'));
    }
}
