<x-client-layout>
    
        <div class="w-full grid grid-cols-1 gap-4">
            <div class="bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8 border border-gray-200">
                <div class="mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Welcome back, {{ Auth::user()->name }}!</h3>
                    <p class="text-base font-normal text-gray-500">Here's what's happening with your Linkadi account today.</p>
                </div>
            </div>
            
            <!-- Expiring Subscriptions Alert -->
            @if($expiringProfiles->isNotEmpty())
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-yellow-800">
                                Subscription Expiring Soon
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p class="mb-2">The following profiles will expire soon:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($expiringProfiles as $profile)
                                        <li>
                                            <strong>{{ $profile->profile_name }}</strong> expires on 
                                            <span class="font-semibold">{{ $profile->expires_at->format('M d, Y') }}</span>
                                            ({{ $profile->expires_at->diffForHumans() }})
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('dashboard.subscriptions.index') }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-yellow-800 bg-yellow-100 hover:bg-yellow-200">
                                    Renew Subscriptions
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Pending Payments Alert -->
            @if($pendingOrders->isNotEmpty())
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-blue-800">
                                You have {{ $pendingOrders->count() }} {{ Str::plural('order', $pendingOrders->count()) }} waiting for payment
                            </h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($pendingOrders as $order)
                                        <li>
                                            Order #{{ $order->id }} - {{ number_format($order->total_price) }} TZS
                                            <a href="{{ route('dashboard.orders.payment', $order) }}" class="underline hover:no-underline">
                                                Pay Now
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif <!-- Profiles Section -->
            <div class="bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8 border border-gray-200">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Your Digital Profiles</h3>
                        <p class="text-base font-normal text-gray-500"> Manage your profiles. Each profile can be linked to a different NFC card. </p>
                    </div> <a href="{{ route('profile.builder.create') }}" class="px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700"> + Create Profile </a>
                </div> @if (Auth::user()->profiles()->count() > 0) <div class="grid grid-cols-1 md:grid-cols-2 gap-4"> @foreach (Auth::user()->profiles as $profile) @php $statusColor = $profile->status_badge_color; @endphp <div class="p-4 border border-gray-200 rounded-lg hover:border-brand-300 transition-colors {{ $profile->is_primary ? 'bg-brand-50 border-brand-300' : '' }}">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="text-lg font-semibold text-gray-900"> {{ $profile->profile_name ?? 'Unnamed Profile' }} </h4> @if($profile->is_primary) <span class="px-2 py-0.5 text-xs font-medium bg-brand-100 text-brand-800 rounded">Primary</span> @endif
                                </div>
                                <p class="text-sm text-gray-500 mb-2"> {{ $profile->slug }} • {{ ucfirst($profile->display_mode ?? 'combined') }} </p> <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 $statusColor }}-900 $statusColor }}-200"> {{ $profile->status_display }} </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-4"> <a href="{{ route('profile.builder.edit', $profile->id) }}" class="flex-1 px-3 py-2 text-sm bg-brand-600 text-white rounded-lg hover:bg-brand-700 text-center"> Edit </a> @if ($profile->canPublish()) <form action="{{ route('profile.publish') }}" method="POST" class="flex-1"> @csrf <input type="hidden" name="profile_id" value="{{ $profile->id }}"> <button type="submit" class="w-full px-3 py-2 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"> Publish </button> </form> @elseif ($profile->isPublished()) <form action="{{ route('profile.unpublish') }}" method="POST" class="flex-1"> @csrf <input type="hidden" name="profile_id" value="{{ $profile->id }}"> <button type="submit" class="w-full px-3 py-2 text-sm bg-gray-600 text-white rounded-lg hover:bg-gray-700"> Unpublish </button> </form> @endif <a href="{{ $profile->public_url }}" target="_blank" class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"> View </a> </div> @if($profile->isPublished()) <div class="mt-3 pt-3 border-t border-gray-200">
                            <p class="text-xs text-gray-500 mb-1">Profile URL:</p>
                            <p class="text-xs font-mono text-gray-700 break-all">{{ $profile->public_url }}</p>
                        </div> @endif
                    </div> @endforeach </div> @else <!-- No Profile Created Yet -->
                <div class="text-center py-8">
                    <p class="text-gray-500 mb-4">You don't have any profiles yet.</p> <a href="{{ route('profile.builder.create') }}" class="inline-block px-6 py-3 bg-brand-600 text-white rounded-lg hover:bg-brand-700"> Create Your First Profile </a>
                </div> @endif
            </div> <!-- Packages Section -->
            <div class="bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8 border border-gray-200">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Available Packages</h3>
                    <p class="text-base font-normal text-gray-500">Choose your NFC card package</p>
                </div> @if($packages->isEmpty()) <div class="text-center py-8">
                    <p class="text-gray-500">No packages available at the moment.</p>
                </div> @else <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"> @foreach($packages as $package) <div class="flex flex-col p-6 border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow"> @if($package->image) <img src="{{ $package->image_url }}" alt="{{ $package->name }}" class="w-full h-40 object-cover rounded-lg mb-4"> @else <div class="w-full h-40 bg-brand-100 rounded-lg mb-4 flex items-center justify-center"> <span class="text-2xl font-bold text-brand-600">{{ substr($package->name, 0, 2) }}</span> </div> @endif <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ $package->name }}</h4> @if($package->description) <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ Str::limit($package->description, 80) }}</p> @endif <div class="mb-4"> @if($package->type === 'classic') @if($package->pricingTiers->isNotEmpty()) <span class="text-2xl font-bold text-gray-900">From TZS {{ number_format($package->pricingTiers->first()->price_per_unit, 2) }}</span> <span class="text-gray-500">/card</span> @endif @else <span class="text-2xl font-bold text-gray-900">TZS {{ number_format($package->base_price ?? 0, 2) }}</span> @endif </div> @if($package->features && count($package->features) > 0) <ul class="mb-4 space-y-2"> @foreach(array_slice($package->features, 0, 3) as $feature) <li class="flex items-center text-sm text-gray-600"> <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg> {{ $feature }} </li> @endforeach </ul> @endif <div class="mt-auto pt-4"> <a href="{{ route('dashboard.cards.checkout', $package->slug) }}" class="block w-full text-center px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition-colors"> Order Now </a> </div>
                    </div> @endforeach </div> @endif
            </div>
        </div>
</x-client-layout>