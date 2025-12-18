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
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Your Digital Profile</h3>
                        <p class="text-base font-normal text-gray-500 dark:text-gray-400">
                            @if (Auth::user()->profile)
                                Your profile is {{ Auth::user()->profile->is_public ? 'public' : 'private' }} and ready to share.
                            @else
                                Create your digital profile to get started with Linkadi.
                            @endif
                        </p>
                    </div>
                    @if (Auth::user()->profile)
                        <a href="{{ route('profile.builder') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Edit Profile
                        </a>
                    @else
                        <a href="{{ route('profile.builder') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Create Profile
                        </a>
                    @endif
                </div>

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
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">QR Code</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Scan this QR code to view your profile, or download it to share with others.
                        </p>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                                <img src="{{ Auth::user()->profile->qr_code_url }}" 
                                     alt="QR Code" 
                                     id="qr-code-image"
                                     class="w-48 h-48">
                            </div>
                            <div class="flex-1">
                                <a href="{{ Auth::user()->profile->qr_code_url }}&download=1" 
                                   download="linkadi-profile-qr-{{ Auth::user()->profile->slug }}.png"
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Download QR Code
                                </a>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                    Click to download a high-resolution QR code (500x500px)
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-dashboard-layout>
