<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderPaymentService
{
    /**
     * Mark an order as paid and update associated profile.
     *
     * @param Order $order
     * @param array $paymentData ['method', 'reference', 'paid_at']
     * @return bool
     * @throws \Exception
     */
    public function markAsPaid(Order $order, array $paymentData = []): bool
    {
        if ($order->isPaymentPaid()) {
            throw new \Exception('Order is already marked as paid.');
        }

        try {
            DB::beginTransaction();

            // Update order payment status
            $order->payment_status = 'paid';
            $order->payment_method = $paymentData['method'] ?? null;
            $order->payment_reference = $paymentData['reference'] ?? null;
            $order->paid_at = $paymentData['paid_at'] ?? now();
            $order->save();

            // Update associated profile if exists
            if ($order->profile_id) {
                $profile = $order->profile;
                
                // Update profile status to paid
                $profile->status = 'paid';
                $profile->order_id = $order->id;
                $profile->package_id = $order->package_id;
                
                // Set expiry date if package has duration
                if ($order->package && isset($order->package->duration_months)) {
                    $profile->expires_at = now()->addMonths($order->package->duration_months);
                }
                
                $profile->save();
            }

            // Log the payment
            Log::info('Order payment received', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'total_price' => $order->total_price,
                'payment_method' => $order->payment_method,
                'payment_reference' => $order->payment_reference,
            ]);

            // Send confirmation email
            // $order->user->notify(new OrderPaidNotification($order));

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark order as paid', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle payment failure.
     *
     * @param Order $order
     * @param string|null $reason
     * @return bool
     */
    public function markAsFailed(Order $order, ?string $reason = null): bool
    {
        try {
            DB::beginTransaction();

            $order->payment_status = 'failed';
            $order->save();

            // Revert profile status if exists
            if ($order->profile_id) {
                $profile = $order->profile;
                if ($profile->isPendingPayment()) {
                    $profile->status = 'ready';
                    $profile->save();
                }
            }

            Log::warning('Order payment failed', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'reason' => $reason,
            ]);

            // Notify user
            // $order->user->notify(new PaymentFailedNotification($order, $reason));

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark order payment as failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle refund.
     *
     * @param Order $order
     * @param string|null $reason
     * @return bool
     * @throws \Exception
     */
    public function handleRefund(Order $order, ?string $reason = null): bool
    {
        if (!$order->isPaymentPaid()) {
            throw new \Exception('Cannot refund an unpaid order.');
        }

        try {
            DB::beginTransaction();

            // Update order status
            $order->payment_status = 'refunded';
            $order->save();

            // Expire associated profile
            if ($order->profile_id) {
                $profile = $order->profile;
                $profile->status = 'expired';
                $profile->is_public = false;
                $profile->save();
            }

            Log::warning('Order refunded', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'total_price' => $order->total_price,
                'reason' => $reason,
            ]);

            // Notify user
            // $order->user->notify(new OrderRefundedNotification($order, $reason));

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle order refund', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Cancel an order.
     *
     * @param Order $order
     * @return bool
     */
    public function cancel(Order $order): bool
    {
        try {
            DB::beginTransaction();

            $order->payment_status = 'cancelled';
            $order->status = 'cancelled';
            $order->save();

            // Revert profile status if exists
            if ($order->profile_id) {
                $profile = $order->profile;
                if ($profile->isPendingPayment()) {
                    $profile->status = 'ready';
                    $profile->save();
                }
            }

            Log::info('Order cancelled', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel order', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle payment webhook from gateway (Stripe/PayPal).
     *
     * @param array $webhookData
     * @return bool
     */
    public function handleWebhook(array $webhookData): bool
    {
        try {
            // Extract order reference from webhook
            $orderReference = $webhookData['order_reference'] ?? null;
            
            if (!$orderReference) {
                throw new \Exception('No order reference in webhook data');
            }

            // Find order
            $order = Order::where('id', $orderReference)
                ->orWhere('payment_reference', $orderReference)
                ->first();

            if (!$order) {
                throw new \Exception('Order not found: ' . $orderReference);
            }

            // Handle different webhook events
            $event = $webhookData['event'] ?? 'payment.success';

            switch ($event) {
                case 'payment.success':
                case 'charge.succeeded':
                    return $this->markAsPaid($order, [
                        'method' => $webhookData['payment_method'] ?? 'online',
                        'reference' => $webhookData['transaction_id'] ?? null,
                        'paid_at' => now(),
                    ]);

                case 'payment.failed':
                case 'charge.failed':
                    return $this->markAsFailed($order, $webhookData['failure_reason'] ?? null);

                case 'payment.refunded':
                case 'charge.refunded':
                    return $this->handleRefund($order, $webhookData['refund_reason'] ?? null);

                default:
                    Log::warning('Unknown webhook event', [
                        'event' => $event,
                        'order_id' => $order->id,
                    ]);
                    return false;
            }
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'webhook_data' => $webhookData,
            ]);
            return false;
        }
    }
}

