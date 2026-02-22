@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block py-2 pl-3 pr-4 text-brand-600 border-b border-brand-600 hover:bg-gray-50 lg:hover:bg-transparent lg:border-0 lg:hover:text-brand-600 lg:p-0 font-medium'
            : 'block py-2 pl-3 pr-4 text-gray-700 border-b border-gray-100 hover:bg-gray-50 lg:hover:bg-transparent lg:border-0 lg:hover:text-brand-600 lg:p-0';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
