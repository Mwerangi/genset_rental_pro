@props([
    'label' => null,
    'name' => '',
    'type' => 'text',
    'required' => false,
    'placeholder' => '',
    'error' => null,
    'value' => old($name, ''),
])

<div {{ $attributes->get('class') ? $attributes->only('class') : '' }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-600">*</span>
            @endif
        </label>
    @endif
    
    <input 
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->except(['class', 'label', 'error'])->merge([
            'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500/30 focus:border-red-500/30 transition-all disabled:bg-gray-100 disabled:cursor-not-allowed'
        ]) }}
    >
    
    @if($error || $errors->has($name))
        <p class="text-red-600 text-sm mt-1">
            {{ $error ?? $errors->first($name) }}
        </p>
    @endif
</div>
