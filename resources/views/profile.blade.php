<x-dashboard-layout>
    <div class="pt-6">
        <div class="w-full space-y-6">
            <div class="bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8 border border-gray-200">
                <h2 class="text-xl font-bold text-gray-900 mb-6"> {{ __('Profile Information') }} </h2>
                <div class="max-w-xl"> <livewire:profile.update-profile-information-form /> </div>
            </div>
            <div class="bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8 border border-gray-200">
                <h2 class="text-xl font-bold text-gray-900 mb-6"> {{ __('Update Password') }} </h2>
                <div class="max-w-xl"> <livewire:profile.update-password-form /> </div>
            </div>
          
        </div>
    </div>
</x-dashboard-layout>