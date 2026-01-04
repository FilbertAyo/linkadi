<x-dashboard-layout>
    <div class="pt-6">
        <div class="w-full grid grid-cols-1 gap-4">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 sm:p-6 xl:p-8 border border-gray-200 dark:border-gray-700">
                <div class="mb-4">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Welcome back, {{ Auth::user()->name }}!</h3>
                    <p class="text-base font-normal text-gray-500 dark:text-gray-400">Here's what's happening with your Linkadi account today.</p>
                </div>
            </div>

            <!-- Profile Status Card -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 sm:p-6 xl:p-8 border border-gray-200 dark:border-gray-700">
                @if (Auth::user()->profile)
                    @php
                        $profile = Auth::user()->profile;
                        $statusColor = $profile->status_badge_color;
                    @endphp
                    
                    <!-- Status Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Your Digital Profile</h3>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 dark:bg-{{ $statusColor }}-900 dark:text-{{ $statusColor }}-200">
                                    {{ $profile->status_display }}
                                </span>
                            </div>
                            
                            <!-- Status Message -->
                            <p class="text-base font-normal text-gray-500 dark:text-gray-400">
                                @if ($profile->isDraft())
                                    Complete your profile to get started with Linkadi.
                                @elseif ($profile->isReady())
                                    Your profile is complete! Select a package to publish it.
                                @elseif ($profile->isPendingPayment())
                                    Payment pending. Complete your payment to publish your profile.
                                @elseif ($profile->isPaid())
                                    Your payment is confirmed! You can now publish your profile.
                                @elseif ($profile->isPublished())
                                    Your profile is live and publicly accessible!
                                @elseif ($profile->isExpired())
                                    Your package has expired. Renew to make your profile public again.
                                @elseif ($profile->isSuspended())
                                    Your profile has been suspended. Please contact support.
                                @endif
                            </p>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            @if ($profile->canPublish())
                                <form action="{{ route('profile.publish') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-semibold shadow-lg hover:shadow-xl transition-all flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        🚀 Publish Profile
                                    </button>
                                </form>
                            @elseif ($profile->isPublished())
                                <form action="{{ route('profile.unpublish') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                                        Unpublish
                                    </button>
                                </form>
                            @endif
                            
                            <a href="{{ route('profile.builder') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                Edit Profile
                            </a>
                        </div>
                    </div>
                    
                    <!-- Progress Bar (for non-published profiles) -->
                    @if (!$profile->isPublished())
                        <div class="mb-4">
                            <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                                <span>Progress to Publishing</span>
                                <span>
                                    @if ($profile->isDraft()) 25%
                                    @elseif ($profile->isReady()) 50%
                                    @elseif ($profile->isPendingPayment() || $profile->isPaid()) 75%
                                    @else 0%
                                    @endif
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500" style="width: 
                                    @if ($profile->isDraft()) 25%
                                    @elseif ($profile->isReady()) 50%
                                    @elseif ($profile->isPendingPayment() || $profile->isPaid()) 75%
                                    @else 0%
                                    @endif
                                "></div>
                            </div>
                        </div>
                    @endif
                @else
                    <!-- No Profile Created Yet -->
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Your Digital Profile</h3>
                            <p class="text-base font-normal text-gray-500 dark:text-gray-400">
                                Create your digital profile to get started with Linkadi.
                            </p>
                        </div>
                        <a href="{{ route('profile.builder') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Create Profile
                        </a>
                    </div>
                @endif

                @if (Auth::user()->profile)
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Profile URL</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white break-all">
                                {{ Auth::user()->profile->public_url }}
                            </p>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Social Links</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ Auth::user()->profile->socialLinks->count() }}
                            </p>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ Auth::user()->profile->is_public ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                                {{ Auth::user()->profile->is_public ? 'Public' : 'Private' }}
                            </span>
                        </div>
                    </div>

                    <!-- QR Code Section -->
                    <div class="mt-6 p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📱 QR Code Downloads</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Download your QR code in multiple formats for printing, sharing, or embedding.
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- PNG Downloads -->
                            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    PNG Format
                                </h5>
                                <div class="space-y-2">
                                    <a href="{{ route('profile.qr.download', ['slug' => Auth::user()->profile->slug, 'format' => 'png', 'size' => 300]) }}" class="block text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                        ⬇️ Small (300x300px) - For web
                                    </a>
                                    <a href="{{ route('profile.qr.download', ['slug' => Auth::user()->profile->slug, 'format' => 'png', 'size' => 500]) }}" class="block text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                        ⬇️ Medium (500x500px) - Standard
                                    </a>
                                    <a href="{{ route('profile.qr.download', ['slug' => Auth::user()->profile->slug, 'format' => 'png', 'size' => 1000]) }}" class="block text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                        ⬇️ Large (1000x1000px) - Print ready
                                    </a>
                                    <a href="{{ route('profile.qr.download', ['slug' => Auth::user()->profile->slug, 'format' => 'png', 'size' => 2000]) }}" class="block text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                                        ⬇️ Extra Large (2000x2000px) - High res
                                    </a>
                                </div>
                            </div>

                            <!-- SVG & PDF -->
                            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
                                <h5 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Special Formats
                                </h5>
                                <div class="space-y-3">
                                    <div>
                                        <a href="{{ route('profile.qr.download', ['slug' => Auth::user()->profile->slug, 'format' => 'svg', 'size' => 500]) }}" class="block text-sm text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 mb-1">
                                            ⬇️ SVG (Vector) - Scalable
                                        </a>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Perfect for logos, infinite scaling</p>
                                    </div>
                                    <div class="pt-2 border-t border-gray-200 dark:border-gray-600">
                                        <a href="{{ route('profile.qr.card', ['slug' => Auth::user()->profile->slug, 'card_size' => 'card']) }}" class="block text-sm text-purple-600 hover:text-purple-800 dark:text-purple-400 mb-1">
                                            🎴 Business Card PDF
                                        </a>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Standard size (3.5" x 2") - Print ready</p>
                                    </div>
                                    <div>
                                        <a href="{{ route('profile.qr.card', ['slug' => Auth::user()->profile->slug, 'card_size' => 'postcard']) }}" class="block text-sm text-purple-600 hover:text-purple-800 dark:text-purple-400 mb-1">
                                            📮 Postcard PDF
                                        </a>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Larger size (6" x 4") - More visible</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- QR Preview -->
                        <div class="flex items-center gap-4 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
                            <img src="{{ Auth::user()->profile->qr_code_url }}" 
                                 alt="QR Code Preview" 
                                 class="w-24 h-24 border-2 border-gray-300 dark:border-gray-600 rounded">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Quick Preview</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Scans to: {{ Auth::user()->profile->public_url }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Packages Section -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 sm:p-6 xl:p-8 border border-gray-200 dark:border-gray-700">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Available Packages</h3>
                    <p class="text-base font-normal text-gray-500 dark:text-gray-400">Choose your NFC card package</p>
                </div>

                @if($packages->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-gray-500 dark:text-gray-400">No packages available at the moment.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($packages as $package)
                            <div class="flex flex-col p-6 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                @if($package->image)
                                    <img src="{{ $package->image_url }}" alt="{{ $package->name }}" class="w-full h-40 object-cover rounded-lg mb-4">
                                @else
                                    <div class="w-full h-40 bg-indigo-100 dark:bg-indigo-900 rounded-lg mb-4 flex items-center justify-center">
                                        <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-300">{{ substr($package->name, 0, 2) }}</span>
                                    </div>
                                @endif

                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $package->name }}</h4>
                                
                                @if($package->description)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">{{ Str::limit($package->description, 80) }}</p>
                                @endif

                                <div class="mb-4">
                                    @if($package->type === 'classic')
                                        @if($package->pricingTiers->isNotEmpty())
                                            <span class="text-2xl font-bold text-gray-900 dark:text-white">From TZS {{ number_format($package->pricingTiers->first()->price_per_unit, 2) }}</span>
                                            <span class="text-gray-500 dark:text-gray-400">/card</span>
                                        @endif
                                    @else
                                        <span class="text-2xl font-bold text-gray-900 dark:text-white">TZS {{ number_format($package->base_price ?? 0, 2) }}</span>
                                    @endif
                                </div>

                                @if($package->features && count($package->features) > 0)
                                    <ul class="mb-4 space-y-2">
                                        @foreach(array_slice($package->features, 0, 3) as $feature)
                                            <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                {{ $feature }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                <div class="mt-auto pt-4">
                                    <a href="{{ route('packages.show', $package->slug) }}" class="block w-full text-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-dashboard-layout>
