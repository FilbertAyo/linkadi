<x-dashboard-layout>
    <div class="pt-6">
        <div class="mb-6">
            <a href="{{ route('orders.index') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 mb-4 inline-block">
                ← Back to Orders
            </a>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Order #{{ $order->id }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Order placed on {{ $order->created_at->format('F d, Y \a\t g:i A') }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Order Details -->
            <div class="lg:col-span-2 space-y-6">
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
            </div>
        </div>
    </div>
</x-dashboard-layout>

