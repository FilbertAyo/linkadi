<x-admin-layout>
    <div class="pt-6">
        <div class="mb-6">
            <a href="{{ route('admin.orders.index') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 mb-4 inline-block">
                ← Back to Orders
            </a>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Order #{{ $order->id }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Order placed on {{ $order->created_at->format('F d, Y \a\t g:i A') }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Order Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Customer Info -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Customer Information</h2>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ $order->user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">{{ $order->user->email }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Package Info -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Package Details</h2>
                    <div class="flex items-start gap-4">
                        @if($order->package->image)
                            <img src="{{ $order->package->image_url }}" alt="{{ $order->package->name }}" class="w-24 h-24 rounded-lg object-cover">
                        @endif
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $order->package->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $order->package->type)) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Order Summary</h2>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-gray-600 dark:text-gray-400">Quantity:</dt>
                            <dd class="text-gray-900 dark:text-white font-medium">{{ $order->quantity }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600 dark:text-gray-400">Unit Price:</dt>
                            <dd class="text-gray-900 dark:text-white font-medium">TZS {{ number_format($order->unit_price, 2) }}</dd>
                        </div>
                        <hr class="border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between">
                            <dt class="text-lg font-semibold text-gray-900 dark:text-white">Total:</dt>
                            <dd class="text-lg font-bold text-indigo-600 dark:text-indigo-400">TZS {{ number_format($order->total_price, 2) }}</dd>
                        </div>
                    </dl>
                </div>

                @if($order->shipping_address)
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Shipping Address</h2>
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $order->shipping_address }}</p>
                    </div>
                @endif

                @if($order->notes)
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Notes</h2>
                        <p class="text-gray-700 dark:text-gray-300">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Order Status -->
            <div>
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Order Status</h2>
                    <div class="mb-4">
                        <span class="px-3 py-1 text-sm font-medium rounded-full 
                            {{ $order->status === 'delivered' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                            {{ $order->status === 'processing' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                            {{ $order->status === 'shipped' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : '' }}
                            {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>

                    <!-- Update Status Form -->
                    <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="mb-4">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Update Status</label>
                            <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Update Status
                        </button>
                    </form>

                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Order Date:</dt>
                            <dd class="text-gray-900 dark:text-white">{{ $order->created_at->format('M d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Last Updated:</dt>
                            <dd class="text-gray-900 dark:text-white">{{ $order->updated_at->format('M d, Y') }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Payment Status -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6 mt-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Payment Status</h2>
                    <div class="mb-4">
                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-{{ $order->payment_status_badge_color }}-100 text-{{ $order->payment_status_badge_color }}-800 dark:bg-{{ $order->payment_status_badge_color }}-900 dark:text-{{ $order->payment_status_badge_color }}-200">
                            {{ $order->payment_status_display }}
                        </span>
                    </div>

                    <dl class="space-y-2 text-sm mb-4">
                        @if($order->payment_method)
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Payment Method:</dt>
                                <dd class="text-gray-900 dark:text-white">{{ ucfirst($order->payment_method) }}</dd>
                            </div>
                        @endif
                        @if($order->payment_reference)
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Reference:</dt>
                                <dd class="text-gray-900 dark:text-white font-mono text-xs">{{ $order->payment_reference }}</dd>
                            </div>
                        @endif
                        @if($order->paid_at)
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Paid At:</dt>
                                <dd class="text-gray-900 dark:text-white">{{ $order->paid_at->format('M d, Y g:i A') }}</dd>
                            </div>
                        @endif
                        @if($order->profile_id)
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Associated Profile:</dt>
                                <dd class="text-gray-900 dark:text-white">
                                    <a href="{{ route('admin.profiles.show', $order->profile_id) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                        View Profile →
                                    </a>
                                </dd>
                            </div>
                        @endif
                    </dl>

                    <!-- Payment Actions -->
                    @if($order->isPaymentPending())
                        <button onclick="document.getElementById('mark-paid-modal').classList.remove('hidden')" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 mb-2">
                            ✓ Mark as Paid
                        </button>
                    @endif

                    @if($order->isPaymentPaid())
                        <button onclick="document.getElementById('refund-modal').classList.remove('hidden')" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            ↩ Refund Order
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Mark as Paid Modal -->
        <div id="mark-paid-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Mark Order as Paid</h3>
                <form method="POST" action="{{ route('admin.orders.mark-paid', $order) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Method</label>
                        <select name="payment_method" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="stripe">Stripe</option>
                            <option value="paypal">PayPal</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Reference (Optional)</label>
                        <input type="text" name="payment_reference" placeholder="Transaction ID or reference" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Confirm Payment
                        </button>
                        <button type="button" onclick="document.getElementById('mark-paid-modal').classList.add('hidden')" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Refund Modal -->
        <div id="refund-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Refund Order</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">This will refund the order and expire the associated profile.</p>
                <form method="POST" action="{{ route('admin.orders.refund', $order) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Refund Reason (Optional)</label>
                        <textarea name="reason" rows="3" placeholder="Enter refund reason..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Process Refund
                        </button>
                        <button type="button" onclick="document.getElementById('refund-modal').classList.add('hidden')" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>

