<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderPaymentService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of all orders.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'package'])->latest();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('package', function ($packageQuery) use ($search) {
                    $packageQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by package type
        if ($request->has('package_type') && $request->package_type) {
            $query->whereHas('package', function ($q) use ($request) {
                $q->where('type', $request->package_type);
            });
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->paginate(15)->withQueryString();

        // Get statistics
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load(['user', 'package', 'package.pricingTiers']);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the order status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,shipped,delivered,cancelled'],
        ]);

        $order->update(['status' => $validated['status']]);

        return redirect()->back()
            ->with('success', 'Order status updated successfully.');
    }

    /**
     * Mark order as paid (manual payment confirmation).
     */
    public function markAsPaid(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_method' => ['nullable', 'string', 'max:50'],
            'payment_reference' => ['required', 'string', 'max:255'],
        ]);

        $paymentService = app(\App\Services\PaymentService::class);
        
        $success = $paymentService->processPayment($order, [
            'method' => $validated['payment_method'] ?? 'manual',
            'reference' => $validated['payment_reference'],
        ]);

        if ($success) {
            return redirect()->back()
                ->with('success', 'Order marked as paid and profiles activated successfully.');
        }

        return redirect()->back()
            ->with('error', 'Failed to process payment.');
    }

    /**
     * Refund an order.
     */
    public function refund(Request $request, Order $order, OrderPaymentService $paymentService)
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $paymentService->handleRefund($order, $validated['reason'] ?? null);

            return redirect()->back()
                ->with('success', 'Order refunded successfully. Associated profile has been expired.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to refund order: ' . $e->getMessage());
        }
    }
}

