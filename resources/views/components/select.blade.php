@props([
    'label' => null,
    'name' => '',
    'required' => false,
    'options' => [],
    'placeholder' => 'Select an option...',
    'error' => null,
    'selected' => old($name, ''),
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
    
    <select 
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->except(['class', 'label', 'error', 'options'])->merge([
            'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500/30 focus:border-red-500/30 transition-all disabled:bg-gray-100 disabled:cursor-not-allowed'
        ]) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        
        @foreach($options as $value => $label)
            <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
        
        {{ $slot }}
    </select>
    
    @if($error || $errors->has($name))
        <p class="text-red-600 text-sm mt-1">
            {{ $error ?? $errors->first($name) }}
        </p>
    @endif
</div>
