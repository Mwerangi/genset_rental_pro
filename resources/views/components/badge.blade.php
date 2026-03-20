@props([
    'variant' => 'default', // default, success, warning, danger, info, new, reviewed, quoted
    'size' => 'md', // sm, md, lg
])

@php
    $variantClasses = [
        'default' => 'bg-gray-100 text-gray-800',
        'success' => 'bg-green-100 text-green-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'danger' => 'bg-red-100 text-red-800',
        'info' => 'bg-blue-100 text-blue-800',
        'new' => 'bg-red-100 text-red-800',
        'reviewed' => 'bg-blue-100 text-blue-800',
        'quoted' => 'bg-green-100 text-green-800',
        'converted' => 'bg-green-100 text-green-800',
        'rejected' => 'bg-gray-100 text-gray-800',
    ];
    
    $sizeClasses = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-3 py-1 text-sm',
        'lg' => 'px-4 py-1.5 text-base',
    ];
    
    $classes = 'inline-flex items-center font-semibold rounded-full ' . 
                $variantClasses[$variant] . ' ' . 
                $sizeClasses[$size];
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
