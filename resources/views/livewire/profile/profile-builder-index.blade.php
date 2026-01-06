<?php

use Livewire\Volt\Component;

new class extends Component {}; ?>

<section>
    <header>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    {{ __('My Profiles') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Create and manage your digital identity profiles. Each profile can be linked to an NFC card or QR code.') }}
                </p>
            </div>
            <a href="{{ route('profile.builder.create') }}" class="px-4 py-2 text-sm font-medium text-white bg-brand-600 rounded-lg hover:bg-brand-700 transition-colors">
                + Create New Profile
            </a>
        </div>
    </header>

    @if (session('status'))
        <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm text-green-800">{{ session('status') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    <div class="mt-6">
        @if (Auth::user()->profiles()->count() > 0)
            <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
                <table class="min-w-full border-collapse border border-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-200">Profile</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-200">Slug</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-200">Display Mode</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-200">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-200">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach (Auth::user()->profiles as $profile)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap border border-gray-200">
                                    <div class="flex items-center">
                                        @if($profile->profile_image)
                                            <img src="{{ asset('storage/' . $profile->profile_image) }}" alt="{{ $profile->profile_name }}" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-brand-100 flex items-center justify-center border border-gray-200">
                                                <span class="text-sm font-bold text-brand-600">
                                                    {{ strtoupper(substr($profile->profile_name ?? Auth::user()->name, 0, 1)) }}
                                                </span>
                                            </div>
                                        @endif
                                        <div class="ml-4">
                                            <div class="flex items-center gap-2">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $profile->profile_name ?? 'Unnamed Profile' }}
                                                </div>
                                                @if($profile->is_primary)
                                                    <span class="px-2 py-0.5 text-xs font-medium bg-brand-100 text-brand-700 rounded-full">Primary</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap border border-gray-200">
                                    <div class="text-sm text-gray-900">
                                        <span class="font-mono text-xs">{{ $profile->slug }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap border border-gray-200">
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded">
                                        {{ ucfirst($profile->display_mode ?? 'combined') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap border border-gray-200">
                                    @if($profile->isPublished())
                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded">Published</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded">
                                            {{ ucfirst($profile->status ?? 'Draft') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium border border-gray-200">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($profile->isPublished())
                                            <a href="{{ $profile->public_url }}" target="_blank" class="text-brand-600 hover:text-brand-900" title="View Profile">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                            </a>
                                        @endif
                                        <a href="{{ route('profile.builder.edit', $profile->id) }}" class="text-brand-600 hover:text-brand-900" title="Edit Profile">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="mt-8 text-center py-12 bg-gray-50 rounded-xl border border-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No profiles yet</h3>
                <p class="mt-2 text-sm text-gray-500">Get started by creating your first digital profile</p>
                <a href="{{ route('profile.builder.create') }}" class="mt-6 inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                    Create Your First Profile
                </a>
            </div>
        @endif
    </div>
</section>
