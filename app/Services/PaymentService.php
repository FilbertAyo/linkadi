<?php

namespace App\Services;

use App\Models\NfcCard;
use App\Models\Order;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Process payment and activate profiles.
     */
    public function processPayment(Order $order, array $paymentData): bool
    {
        try {
            DB::transaction(function () use ($order, $paymentData) {
                // Update order payment status
                $order->update([
                    'payment_status' => 'paid',
                    'payment_method' => $paymentData['method'] ?? 'manual',
                    'payment_reference' => $paymentData['reference'] ?? null,
                    'paid_at' => now(),
                    'status' => 'processing',
                ]);
                
                // Activate all NFC cards and their profiles
                $nfcCards = NfcCard::where('order_id', $order->id)->get();
                
                foreach ($nfcCards as $card) {
                    // Activate card
                    $card->update([
                        'status' => 'pending_production',
                        'activated_at' => now(),
                        'expires_at' => now()->addYear(),
                    ]);
                    
                    // Activate profile
                    $profile = $card->profile;
                    if ($profile) {
                        $profile->activate(12); // 12 months subscription
                    }
                }
                
                Log::info('Payment processed successfully', [
                    'order_id' => $order->id,
                    'amount' => $order->total_price,
                    'method' => $paymentData['method'] ?? 'manual',
                ]);
            });
            
            return true;
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Handle webhook from payment gateway.
     */
    public function handleWebhook(string $provider, array $payload): bool
    {
        try {
            // Verify webhook signature (implement per provider)
            $isValid = $this->verifyWebhookSignature($provider, $payload);
            
            if (!$isValid) {
                Log::warning('Invalid webhook signature', ['provider' => $provider]);
                return false;
            }
            
            // Extract order reference from payload
            $orderReference = $this->extractOrderReference($provider, $payload);
            $order = Order::find($orderReference);
            
            if (!$order) {
                Log::warning('Order not found in webhook', ['reference' => $orderReference]);
                return false;
            }
            
            // Check payment status
            $paymentStatus = $this->extractPaymentStatus($provider, $payload);
            
            if ($paymentStatus === 'success') {
                return $this->processPayment($order, [
                    'method' => $provider,
                    'reference' => $this->extractTransactionId($provider, $payload),
                ]);
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
    
    /**
     * Verify webhook signature.
     */
    protected function verifyWebhookSignature(string $provider, array $payload): bool
    {
        // Implement signature verification per provider
        // For now, return true (implement in production)
        return true;
    }
    
    /**
     * Extract order reference from webhook payload.
     */
    protected function extractOrderReference(string $provider, array $payload): ?string
    {
        return match($provider) {
            'mpesa' => $payload['order_id'] ?? null,
            'stripe' => $payload['metadata']['order_id'] ?? null,
            default => null,
        };
    }
    
    /**
     * Extract payment status from webhook payload.
     */
    protected function extractPaymentStatus(string $provider, array $payload): string
    {
        return match($provider) {
            'mpesa' => $payload['status'] === 'completed' ? 'success' : 'failed',
            'stripe' => $payload['status'] === 'succeeded' ? 'success' : 'failed',
            default => 'unknown',
        };
    }
    
    /**
     * Extract transaction ID from webhook payload.
     */
    protected function extractTransactionId(string $provider, array $payload): ?string
    {
        return match($provider) {
            'mpesa' => $payload['transaction_id'] ?? null,
            'stripe' => $payload['id'] ?? null,
            default => null,
        };
    }
    
    /**
     * Initiate M-Pesa payment.
     */
    public function initiateMpesaPayment(Order $order, string $phoneNumber): array
    {
        // Placeholder for M-Pesa STK Push implementation
        return [
            'success' => false,
            'message' => 'M-Pesa integration coming soon',
            'checkout_request_id' => null,
        ];
    }
    
    /**
     * Initiate Stripe payment.
     */
    public function initiateStripePayment(Order $order): array
    {
        // Placeholder for Stripe payment intent
        return [
            'success' => false,
            'message' => 'Stripe integration coming soon',
            'client_secret' => null,
        ];
    }
}

