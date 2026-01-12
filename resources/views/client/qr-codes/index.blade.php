<x-client-layout>
    <div class="pt-6">
        <div class="w-full grid grid-cols-1 gap-4">
            <!-- Page Header -->
            <div class="bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8 border border-gray-200">
                <div class="mb-4">
                    <h3 class="text-xl font-bold text-gray-900">QR Code Downloads</h3>
                    <p class="text-base font-normal text-gray-500">Download QR codes for your profiles in multiple formats.</p>
                </div>
            </div>

            @if($profiles->isEmpty())
                <!-- No Profiles -->
                <div class="bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8 border border-gray-200">
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No profiles found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a profile.</p>
                        <div class="mt-6">
                            <a href="{{ route('profile.builder.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Create Profile
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <!-- QR Codes for Each Profile -->
                @foreach($profiles as $profile)
                    <div class="bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8 border border-gray-200">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-lg font-semibold text-gray-900">{{ $profile->profile_name }}</h4>
                                    @if($profile->is_primary)
                                        <span class="px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-800 rounded">Primary</span>
                                    @endif
                                    @php
                                        $statusColor = $profile->status_badge_color;
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                        {{ $profile->status_display }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">{{ $profile->public_url }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- QR Code Preview -->
                            <div class="md:col-span-1">
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <img src="{{ $profile->qr_code_url }}" alt="QR Code for {{ $profile->profile_name }}" class="w-full aspect-square border-2 border-gray-300 rounded mb-3">
                                    <p class="text-xs text-center text-gray-500">Scans to your profile</p>
                                </div>
                            </div>

                            <!-- Download Options -->
                            <div class="md:col-span-2 space-y-4">
                                <!-- PNG & SVG Downloads -->
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <h5 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Download Formats
                                    </h5>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <button onclick="openPreviewModal('{{ $profile->slug }}', 'png', 500, '{{ addslashes($profile->profile_name) }}', '{{ $profile->public_url }}')" class="px-3 py-2 text-sm bg-white text-indigo-600 border border-indigo-300 rounded-lg hover:bg-indigo-50 transition-colors">
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                PNG (500x500px)
                                            </span>
                                        </button>
                                        <button onclick="openPreviewModal('{{ $profile->slug }}', 'svg', 500, '{{ addslashes($profile->profile_name) }}', '{{ $profile->public_url }}')" class="px-3 py-2 text-sm bg-white text-emerald-600 border border-emerald-300 rounded-lg hover:bg-emerald-50 transition-colors">
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                SVG (Vector)
                                            </span>
                                        </button>
                                    </div>
                                </div>

                           
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50" onclick="closePreviewModal(event)">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-lg bg-white" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">QR Code Preview</h3>
                <button onclick="closePreviewModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="mb-6">
                <div class="bg-gray-50 rounded-lg p-6 flex items-center justify-center">
                    <img id="previewImage" src="" alt="QR Code Preview" class="max-w-full h-auto border-4 border-gray-300 rounded-lg">
                    <object id="previewObject" data="" type="image/svg+xml" class="hidden max-w-full h-auto border-4 border-gray-300 rounded-lg"></object>
                </div>
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600" id="previewInfo"></p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3">
                <button onclick="closePreviewModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
                <a id="downloadButton" href="#" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download
                </a>
            </div>
        </div>
    </div>

    <!-- JavaScript for Modal -->
    <script>
        async function openPreviewModal(slug, format, size, profileName, publicUrl) {
            const modal = document.getElementById('previewModal');
            const modalTitle = document.getElementById('modalTitle');
            const previewImage = document.getElementById('previewImage');
            const previewObject = document.getElementById('previewObject');
            const previewInfo = document.getElementById('previewInfo');
            const downloadButton = document.getElementById('downloadButton');

            // Build the download URL using the route pattern (for actual download)
            const baseUrl = '{{ url("/") }}';
            const downloadUrl = `${baseUrl}/profile/${slug}/qr/download?format=${format}&size=${size}`;

            // Set modal content
            modalTitle.textContent = `${profileName} - ${format.toUpperCase()} (${size}x${size}px)`;
            previewInfo.textContent = `Format: ${format.toUpperCase()} | Size: ${size}x${size}px | Perfect for ${getSizeUseCase(size)}`;
            downloadButton.href = downloadUrl;

            // Build the preview URL
            if (format === 'svg') {
                // For SVG, fetch the content and create a blob URL for preview
                try {
                    const response = await fetch(downloadUrl);
                    const svgContent = await response.text();
                    const blob = new Blob([svgContent], { type: 'image/svg+xml' });
                    const blobUrl = URL.createObjectURL(blob);
                    
                    // Hide img, show object tag for SVG
                    previewImage.classList.add('hidden');
                    previewObject.classList.remove('hidden');
                    previewObject.data = blobUrl;
                } catch (error) {
                    console.error('Error loading SVG:', error);
                    // Fallback: show img with download URL
                    previewImage.classList.remove('hidden');
                    previewObject.classList.add('hidden');
                    previewImage.src = downloadUrl;
                }
            } else {
                // For PNG, use external QR code service for preview (works better than download URL)
                const previewUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(publicUrl)}`;
                // Show img, hide object tag for PNG
                previewImage.classList.remove('hidden');
                previewObject.classList.add('hidden');
                previewImage.src = previewUrl;
            }

            // Show modal
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closePreviewModal(event) {
            // Only close if clicking on the backdrop, not if event is undefined (called from button)
            if (event && event.target.id !== 'previewModal') {
                return;
            }
            const modal = document.getElementById('previewModal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function getSizeUseCase(size) {
            switch(size) {
                case 300:
                    return 'web use and social media';
                case 500:
                    return 'standard printing and presentations';
                case 1000:
                    return 'high-quality prints and posters';
                case 2000:
                    return 'professional printing and large formats';
                default:
                    return 'general use';
            }
        }

        // Close modal on ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modal = document.getElementById('previewModal');
                if (!modal.classList.contains('hidden')) {
                    closePreviewModal();
                }
            }
        });
    </script>
</x-client-layout>
