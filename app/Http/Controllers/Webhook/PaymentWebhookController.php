<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    protected $paymentService;
    
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    
    /**
     * Handle M-Pesa webhook.
     */
    public function mpesa(Request $request)
    {
        Log::info('M-Pesa webhook received', $request->all());
        
        $success = $this->paymentService->handleWebhook('mpesa', $request->all());
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Webhook processed' : 'Webhook processing failed',
        ]);
    }
    
    /**
     * Handle Stripe webhook.
     */
    public function stripe(Request $request)
    {
        Log::info('Stripe webhook received', $request->all());
        
        $success = $this->paymentService->handleWebhook('stripe', $request->all());
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Webhook processed' : 'Webhook processing failed',
        ]);
    }
    
    /**
     * Manual payment confirmation (admin only).
     */
    public function manualConfirm(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_reference' => 'required|string|max:255',
        ]);
        
        $order = \App\Models\Order::findOrFail($validated['order_id']);
        
        $success = $this->paymentService->processPayment($order, [
            'method' => 'manual',
            'reference' => $validated['payment_reference'],
        ]);
        
        if ($success) {
            return redirect()->back()->with('success', 'Payment confirmed successfully.');
        }
        
        return redirect()->back()->with('error', 'Failed to confirm payment.');
    }
}
