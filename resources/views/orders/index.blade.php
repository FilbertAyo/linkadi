<x-client-layout>
    <div class="pt-6">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">My Orders</h1>
            <p class="text-gray-600 mt-2">View your order history</p>
        </div> @if($orders->isEmpty()) <div class="bg-white shadow rounded-lg p-8 text-center border border-gray-200">
            <p class="text-gray-500 mb-4">You haven't placed any orders yet.</p> <a href="{{ route('welcome') }}#pricing" class="inline-block px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"> Browse Packages </a>
        </div> @else <div class="bg-white shadow rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Package</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200"> @foreach($orders as $order) <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"> #{{ $order->id }} </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"> @if($order->package) {{ $order->package->name }} @else <span class="text-gray-400 italic">Package Deleted</span> @endif </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"> {{ $order->quantity }} </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"> TZS {{ number_format($order->total_price, 2) }} </td>
                            <td class="px-6 py-4 whitespace-nowrap"> <span class="px-2 py-1 text-xs font-medium rounded-full {{ $order->status === 'delivered' ? 'bg-green-100 text-green-800' : '' }} {{ $order->status === 'processing' ? 'bg-brand-100 text-brand-800' : '' }} {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }} {{ $order->status === 'shipped' ? 'bg-purple-100 text-purple-800' : '' }} {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}"> {{ ucfirst($order->status) }} </span> </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"> {{ $order->created_at->format('M d, Y') }} </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"> <a href="{{ route('orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900"> View </a> </td>
                        </tr> @endforeach </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200"> {{ $orders->links() }} </div>
        </div> @endif
    </div>
</x-client-layout>