<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $profile->user->name }} - {{ config('app.name', 'Linkadi') }}</title>
    <meta name="description" content="{{ $profile->bio ? \Illuminate\Support\Str::limit($profile->bio, 160) : 'View ' . $profile->user->name . '\'s digital profile on Linkadi.' }}"> <!-- Open Graph / Facebook -->
    <meta property="og:type" content="profile">
    <meta property="og:url" content="{{ url('/p/' . $profile->slug) }}">
    <meta property="og:title" content="{{ $profile->user->name }} - {{ config('app.name', 'Linkadi') }}">
    <meta property="og:description" content="{{ $profile->bio ? \Illuminate\Support\Str::limit($profile->bio, 200) : 'View ' . $profile->user->name . '\'s digital profile' }}"> @if($profile->profile_image)
    <meta property="og:image" content="{{ asset('storage/' . $profile->profile_image) }}"> @endif <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/p/' . $profile->slug) }}">
    <meta property="twitter:title" content="{{ $profile->user->name }} - {{ config('app.name', 'Linkadi') }}">
    <meta property="twitter:description" content="{{ $profile->bio ? Str::limit($profile->bio, 200) : 'View ' . $profile->user->name . '\'s digital profile' }}"> @if($profile->profile_image)
    <meta property="twitter:image" content="{{ asset('storage/' . $profile->profile_image) }}"> @endif <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" /> <!-- Scripts --> @vite(['resources/css/app.css', 'resources/js/app.js']) <!-- Primary Color Styles -->
    <style>
        .primary-color {
            color: #8e1616;
        }

        .primary-bg {
            background-color: #8e1616;
        }

        .primary-bg-hover:hover {
            background-color: #b01e1e;
        }

        .primary-hover:hover {
            color: #8e1616;
        }

        .primary-border {
            border-color: #8e1616;
        }

        .primary-gradient {
            background: linear-gradient(to right, #8e1616, #b01e1e);
        }

        .primary-avatar-bg {
            background-color: #f8d7d7;
        }

        .group:hover .primary-color-hover {
            color: #8e1616;
        }

        @media (prefers-color-scheme: dark) {
            .primary-hover:hover {
                color: #d13535;
            }

            .group:hover .primary-color-hover {
                color: #d13535;
            }

            .primary-avatar-bg {
                background-color: #5a0f0f;
            }

            .primary-color {
                color: #d13535;
            }
        }
    </style>
</head>

<body class="h-full bg-gray-50">
    <div class="min-h-screen"> <!-- Cover Image --> @if($profile->cover_image) <div class="h-64 bg-cover bg-center" style="background-image: url('{{ asset('storage/' . $profile->cover_image) }}')"></div> @else <div class="h-32 primary-gradient"></div> @endif <!-- Profile Container -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 pb-12">
            <div class="bg-white rounded-lg shadow-xl overflow-hidden"> <!-- Profile Header -->
                <div class="px-6 sm:px-8 pt-8 pb-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6"> <!-- Profile Image --> @if($profile->profile_image) <img src="{{ asset('storage/' . $profile->profile_image) }}" alt="{{ $profile->user->name }}" class="h-32 w-32 rounded-full border-4 border-white shadow-lg object-cover"> @else <div class="h-32 w-32 rounded-full border-4 border-white shadow-lg flex items-center justify-center primary-avatar-bg"> <span class="text-4xl font-bold primary-color"> {{ strtoupper(substr($profile->user->name, 0, 1)) }} </span> </div> @endif <!-- Profile Info -->
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h1 class="text-3xl font-bold text-gray-900"> @if($profile->displaysCompanyOnly() && $profile->business_name) {{ $profile->business_name }} @elseif($profile->displaysPersonalOnly()) {{ $profile->user->name }} @else {{ $profile->display_name }} @endif </h1> @if($profile->isBusiness() && !$profile->displaysPersonalOnly()) <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"> Business </span> @endif
                            </div> @if($profile->subtitle && !$profile->displaysCompanyOnly()) <p class="text-lg text-gray-600 mb-4"> @if($profile->displaysPersonalOnly()) @if($profile->title && $profile->company) {{ $profile->title }} at {{ $profile->company }} @elseif($profile->title) {{ $profile->title }} @elseif($profile->company) {{ $profile->company }} @endif @else {{ $profile->subtitle }} @endif </p> @endif @if($profile->isBusiness() && $profile->tax_id && !$profile->displaysPersonalOnly()) <p class="text-sm text-gray-500"> Tax ID: {{ $profile->tax_id }} </p> @endif @if($profile->displaysCompanyOnly() && $profile->title) <p class="text-lg text-gray-600 mb-4"> Contact: {{ $profile->title }} {{ $profile->user->name ? '- ' . $profile->user->name : '' }} </p> @endif
                        </div>
                    </div> <!-- Add to Contacts Button -->
                    <div class="mt-6"> <a href="{{ route('profile.vcard', $profile->slug) }}" class="w-full inline-flex items-center justify-center gap-3 px-6 py-3 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all primary-bg primary-bg-hover"> <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg> <span>Save to Contacts</span> </a>
                        <p class="text-xs text-gray-500 mt-2 text-center"> Download contact card (.vcf) - Works on all devices </p>
                    </div>
                </div> <!-- Bio Section --> @if($profile->bio) <div class="px-6 sm:px-8 pb-6 border-t border-gray-200 pt-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">About</h2>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $profile->bio }}</p>
                </div> @endif <!-- Contact Information --> @php $publicContacts = $profile->contacts()->public()->get(); $phones = $publicContacts->where('type', 'phone'); $emails = $publicContacts->where('type', 'email'); // Filter contacts based on display mode // For personal_only: show only personal contacts (if labeled as personal) // For company_only: show only company contacts (if labeled as company) // For combined: show all if ($profile->displaysPersonalOnly()) { $phones = $phones->filter(function($contact) { return !$contact->is_company || $contact->label === 'Personal'; }); $emails = $emails->filter(function($contact) { return !$contact->is_company || $contact->label === 'Personal'; }); } elseif ($profile->displaysCompanyOnly()) { $phones = $phones->filter(function($contact) { return $contact->is_company || $contact->label === 'Company' || $contact->label === 'Business'; }); $emails = $emails->filter(function($contact) { return $contact->is_company || $contact->label === 'Company' || $contact->label === 'Business'; }); } // Show website/address based on display mode $showWebsite = $profile->website && ( $profile->displaysCombined() || ($profile->displaysCompanyOnly() && $profile->isBusiness()) || ($profile->displaysPersonalOnly() && !$profile->isBusiness()) ); $showAddress = $profile->address && ( $profile->displaysCombined() || ($profile->displaysCompanyOnly() && $profile->isBusiness()) || ($profile->displaysPersonalOnly() && !$profile->isBusiness()) ); @endphp @if($phones->isNotEmpty() || $emails->isNotEmpty() || $showWebsite || $showAddress) <div class="px-6 sm:px-8 pb-6 border-t border-gray-200 pt-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Contact Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4"> @foreach($phones as $phone) <div class="flex items-center gap-3"> <span class="text-lg">{{ $phone->icon }}</span>
                            <div class="flex-1"> <a href="tel:{{ $phone->value }}" class="text-gray-700 primary-hover transition-colors"> {{ $phone->value }} </a>
                                <p class="text-xs text-gray-500">{{ $phone->display_label }}</p>
                            </div>
                        </div> @endforeach @foreach($emails as $email) <div class="flex items-center gap-3"> <span class="text-lg">{{ $email->icon }}</span>
                            <div class="flex-1"> <a href="mailto:{{ $email->value }}" class="text-gray-700 break-all primary-hover transition-colors"> {{ $email->value }} </a>
                                <p class="text-xs text-gray-500">{{ $email->display_label }}</p>
                            </div>
                        </div> @endforeach @if($showWebsite) <div class="flex items-center gap-3"> <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                            </svg> <a href="{{ $profile->website }}" target="_blank" rel="noopener noreferrer" class="text-gray-700 primary-hover transition-colors"> {{ parse_url($profile->website, PHP_URL_HOST) ?: $profile->website }} </a> </div> @endif @if($showAddress) <div class="flex items-center gap-3"> <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg> <span class="text-gray-700">{{ $profile->address }}</span> </div> @endif </div>
                </div> @endif <!-- Social Links --> @if($profile->socialLinks->count() > 0) <div class="px-6 sm:px-8 pb-8 border-t border-gray-200 pt-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Links</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3"> @foreach($profile->socialLinks as $link) <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors group">
                            <div class="flex-shrink-0"> @if($link->platform === 'linkedin') <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                                </svg> @elseif($link->platform === 'twitter') <svg class="w-6 h-6 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                                </svg> @elseif($link->platform === 'github') <svg class="w-6 h-6 text-gray-800" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                                </svg> @elseif($link->platform === 'instagram') <svg class="w-6 h-6 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                                </svg> @elseif($link->platform === 'facebook') <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                </svg> @elseif($link->platform === 'youtube') <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                                </svg> @elseif($link->platform === 'tiktok') <svg class="w-6 h-6 text-gray-900" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z" />
                                </svg> @else <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                </svg> @endif </div> <span class="font-medium text-gray-900 primary-color-hover transition-colors"> {{ $link->label }} </span>
                        </a> @endforeach </div>
                </div> @endif <!-- Footer -->
                <div class="px-6 sm:px-8 py-4 bg-gray-50 border-t border-gray-200">
                    <p class="text-sm text-center text-gray-500"> Powered by <a href="{{ route('welcome') }}" class="hover:underline primary-color">Linkadi</a> </p>
                </div>
            </div>
        </div>
    </div> <!-- Dark mode toggle script -->
 
</body>

</html>