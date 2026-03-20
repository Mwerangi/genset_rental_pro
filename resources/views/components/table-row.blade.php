@props([
    'striped' => false,
])

@php
    $classes = $striped ? 'even:bg-gray-50' : '';
@endphp

<tr {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</tr>
