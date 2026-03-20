@props([
    'striped' => true,
])

@php
    $classes = 'min-w-full divide-y divide-gray-200';
@endphp

<div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </table>
</div>
