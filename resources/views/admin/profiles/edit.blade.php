<x-admin-layout>
    <div class="pt-6">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit Profile</h1>
            <p class="text-gray-600 mt-2">Update profile information</p>
        </div>

        <!-- Profile Owner Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $profile->user->name }}</h3>
                    <p class="text-sm text-gray-600">{{ $profile->user->email }}</p>
                    <p class="text-sm text-gray-500 mt-1">Profile: <span class="font-medium">{{ $profile->display_name }}</span></p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg border border-gray-200 p-6">
            <form method="POST" action="{{ route('admin.profiles.update', $profile) }}">
                @csrf
                @method('PUT')

                <!-- Current Status Display -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Current Status</h3>
                    <div class="flex items-center gap-4">
                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-{{ $profile->status_badge_color }}-100 text-{{ $profile->status_badge_color }}-800">
                            {{ $profile->status_display }}
                        </span>
                        @if($profile->published_at)
                            <span class="text-sm text-gray-600">Published: {{ $profile->published_at->format('M d, Y') }}</span>
                        @endif
                        @if($profile->expires_at)
                            <span class="text-sm text-gray-600">Expires: {{ $profile->expires_at->format('M d, Y') }}</span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Status Field -->
                    <div class="md:col-span-2 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <label for="status" class="block text-sm font-semibold text-gray-900 mb-2">
                            Profile Status
                            <span class="text-xs font-normal text-gray-600">(Admin Control)</span>
                        </label>
                        <select name="status" id="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500">
                            <option value="draft" {{ old('status', $profile->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="ready" {{ old('status', $profile->status) === 'ready' ? 'selected' : '' }}>Ready</option>
                            <option value="pending_payment" {{ old('status', $profile->status) === 'pending_payment' ? 'selected' : '' }}>Pending Payment</option>
                            <option value="paid" {{ old('status', $profile->status) === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="published" {{ old('status', $profile->status) === 'published' ? 'selected' : '' }}>Published (Active)</option>
                            <option value="expired" {{ old('status', $profile->status) === 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="suspended" {{ old('status', $profile->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                        <p class="mt-2 text-xs text-gray-600">
                            <strong>Note:</strong> Setting status to "Published" will make the profile active and accessible. If no expiration date is set, it will default to 12 months from now.
                        </p>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Profile Information -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $profile->title) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500" placeholder="e.g., CEO, Developer">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="company" class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                        <input type="text" name="company" id="company" value="{{ old('company', $profile->company) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500" placeholder="Company name">
                        @error('company')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                        <textarea name="bio" id="bio" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500" placeholder="Profile bio or description">{{ old('bio', $profile->bio) }}</textarea>
                        @error('bio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $profile->phone) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500" placeholder="+1234567890">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $profile->email) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500" placeholder="email@example.com">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="website" class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                        <input type="url" name="website" id="website" value="{{ old('website', $profile->website) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500" placeholder="https://example.com">
                        @error('website')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <input type="text" name="address" id="address" value="{{ old('address', $profile->address) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-indigo-500" placeholder="Street, City, Country">
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_public" value="1" {{ old('is_public', $profile->is_public) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Make profile public (visible to everyone)</span>
                        </label>
                        @error('is_public')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Update Profile
                    </button>
                    <a href="{{ route('admin.profiles.index') }}" class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
