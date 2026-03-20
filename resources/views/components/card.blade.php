@props([
    'title' => null,
    'subtitle' => null,
    'accent' => false, // Add red accent
    'padding' => 'p-6',
])

@php
    $borderClass = $accent ? 'border-l-4 border-red-600' : '';
    $classes = 'bg-white rounded-lg shadow-md border border-gray-200 ' . $borderClass . ' ' . $padding;
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if($title || $subtitle)
        <div class="mb-4">
            @if($title)
                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
            @endif
            @if($subtitle)
                <p class="text-sm text-gray-600 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    
    {{ $slot }}
</div>
