<button {{ $attributes->merge(['type' => 'submit', 'class' => 'text-white bg-brand-600 hover:bg-brand-700 focus:ring-4 focus:ring-brand-200 font-medium rounded-lg text-base px-5 py-3 w-full sm:w-auto text-center inline-flex items-center justify-center']) }}>
    {{ $slot }}
</button>
