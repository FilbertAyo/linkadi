<x-client-layout>
    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Success Message -->
        <div class="bg-green-50 border-l-4 border-green-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">
                        <strong>Profile Created Successfully!</strong> Now select a package to order your NFC card and activate your profile.
                    </p>
                </div>
            </div>
        </div>

        <!-- Profile Summary -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Profile</h3>
            <div class="flex items-center gap-4">
                @if($profile->profile_image)
                    <img src="{{ asset('storage/' . $profile->profile_image) }}" alt="Profile" class="w-16 h-16 rounded-full object-cover border-2 border-gray-200">
                @else
                    <div class="w-16 h-16 rounded-full bg-brand-100 flex items-center justify-center">
                        <span class="text-2xl font-bold text-brand-600">{{ substr($profile->profile_name ?? 'P', 0, 1) }}</span>
                    </div>
                @endif
                <div>
                    <h4 class="text-lg font-medium text-gray-900">{{ $profile->profile_name }}</h4>
                    <p class="text-sm text-gray-500">{{ config('app.url') }}/p/{{ $profile->slug }}</p>
                    <span class="inline-flex mt-1 items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        Pending Payment
                    </span>
                </div>
            </div>
        </div>

        <!-- Package Selection -->
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Choose Your NFC Card Package</h2>
            <p class="text-gray-600 mb-6">Select a package that suits your needs. All packages include physical NFC card + QR code.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($packages as $package)
                    <div class="bg-white rounded-lg shadow-sm border-2 border-gray-200 hover:border-brand-500 hover:shadow-lg transition-all p-6">
                        @if($package->image)
                            <img src="{{ $package->image_url }}" alt="{{ $package->name }}" class="w-full h-40 object-cover rounded-lg mb-4">
                        @else
                            <div class="w-full h-40 bg-brand-100 rounded-lg mb-4 flex items-center justify-center">
                                <span class="text-3xl font-bold text-brand-600">{{ substr($package->name, 0, 2) }}</span>
                            </div>
                        @endif

                        <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $package->name }}</h3>
                        
                        @if($package->description)
                            <p class="text-sm text-gray-600 mb-4">{{ Str::limit($package->description, 100) }}</p>
                        @endif

                        <div class="mb-4">
                            @php
                                $subscriptionOptions = $package->getSubscriptionOptions();
                                $firstOption = $subscriptionOptions[0] ?? null;
                            @endphp
                            @if($firstOption)
                                <div class="text-3xl font-bold text-gray-900">
                                    {{ number_format($firstOption['price']) }} <span class="text-base font-normal text-gray-500">TZS</span>
                                </div>
                                <p class="text-sm text-gray-500">for {{ $firstOption['years'] }} {{ Str::plural('year', $firstOption['years']) }}</p>
                                
                                @if(count($subscriptionOptions) > 1)
                                    <p class="text-xs text-brand-600 mt-1">Multi-year plans available</p>
                                @endif
                            @endif
                        </div>

                        @if($package->features && count($package->features) > 0)
                            <ul class="space-y-2 mb-6">
                                @foreach(array_slice($package->features, 0, 4) as $feature)
                                    <li class="flex items-start text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <a href="{{ route('profile.order-form', ['profile' => $profile->id, 'package' => $package->id]) }}" class="block w-full text-center px-4 py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg transition-colors">
                            Select Package
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Skip for now option -->
        <div class="text-center">
            <a href="{{ route('profile.builder.edit', $profile->id) }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                Skip for now, I'll order later
            </a>
        </div>
    </div>
</x-client-layout>

