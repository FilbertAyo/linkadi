<x-client-layout>
    <div class="space-y-6"> <!-- Page Header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight"> My Orders </h2>
                <p class="mt-1 text-sm text-gray-500"> Track your NFC card orders and deliveries </p>
            </div>
        </div> <!-- Pending Payment Alert --> @php $pendingPaymentOrders = auth()->user()->orders()->where('payment_status', 'pending')->get(); @endphp @if($pendingPaymentOrders->count() > 0) <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-start"> <svg class="h-5 w-5 text-yellow-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-yellow-800"> Pending Payments </h3>
                    <p class="mt-1 text-sm text-yellow-700"> You have {{ $pendingPaymentOrders->count() }} {{ Str::plural('order', $pendingPaymentOrders->count()) }} waiting for payment </p>
                    <div class="mt-2 flex flex-wrap gap-2"> @foreach($pendingPaymentOrders->take(3) as $pendingOrder) <a href="{{ route('dashboard.orders.payment', $pendingOrder) }}" class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-yellow-100 text-yellow-800 hover:bg-yellow-200"> Order #{{ $pendingOrder->id }} - {{ number_format($pendingOrder->total_price) }} TZS </a> @endforeach </div>
                </div>
            </div>
        </div> @endif <!-- Filters -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4">
            <form method="GET" class="flex flex-wrap gap-4">
                <div> <label for="payment_status" class="sr-only">Payment Status</label> <select name="payment_status" id="payment_status" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
                        <option value="all" {{ request('payment_status', 'all') === 'all' ? 'selected' : '' }}> All Payments ({{ $paymentStatusCounts['all'] }}) </option>
                        <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}> Pending ({{ $paymentStatusCounts['pending'] }}) </option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}> Paid ({{ $paymentStatusCounts['paid'] }}) </option>
                    </select> </div>
                <div> <label for="status" class="sr-only">Order Status</label> <select name="status" id="status" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
                        <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}> All Orders ({{ $statusCounts['all'] }}) </option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}> Pending ({{ $statusCounts['pending'] }}) </option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}> Processing ({{ $statusCounts['processing'] }}) </option>
                        <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}> Shipped ({{ $statusCounts['shipped'] }}) </option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}> Delivered ({{ $statusCounts['delivered'] }}) </option>
                    </select> </div>
            </form>
        </div> <!-- Orders List --> @if($orders->isEmpty()) <div class="text-center py-12 bg-white rounded-lg border border-gray-200"> <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No orders yet</h3>
            <p class="mt-1 text-sm text-gray-500"> Start by ordering your first NFC card! </p>
            <div class="mt-6"> <a href="{{ route('dashboard.cards.packages') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700"> Order NFC Cards </a> </div>
        </div> @else <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
            <ul role="list" class="divide-y divide-gray-200"> @foreach($orders as $order) <li class="p-4 sm:p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-3">
                                <p class="text-sm font-medium text-gray-900"> Order #{{ $order->id }} </p> <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $order->payment_status_badge_color }}-100 text-{{ $order->payment_status_badge_color }}-800 $order->payment_status_badge_color }}-900/20 $order->payment_status_badge_color }}-400"> {{ $order->payment_status_display }} </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500"> {{ $order->quantity }} {{ Str::plural('card', $order->quantity) }} • {{ $order->package->name }} • {{ $order->created_at->format('M d, Y') }} </p>
                            <div class="mt-2 flex items-center text-sm text-gray-500"> @foreach($order->nfcCards->take(3) as $card) <span class="mr-2">{{ $card->profile->profile_name ?? $card->profile->slug }}</span> @endforeach @if($order->nfcCards->count() > 3) <span>+{{ $order->nfcCards->count() - 3 }} more</span> @endif </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900"> {{ number_format($order->total_price) }} TZS </p> @if($order->payment_status === 'pending') <a href="{{ route('dashboard.orders.payment', $order) }}" class="text-sm text-brand-600 hover:text-brand-700"> Complete Payment </a> @endif
                            </div> <a href="{{ route('dashboard.orders.show', $order) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"> View </a>
                        </div>
                    </div>
                </li> @endforeach </ul>
        </div> <!-- Pagination -->
        <div class="mt-6"> {{ $orders->links() }} </div> @endif
    </div>
</x-client-layout>