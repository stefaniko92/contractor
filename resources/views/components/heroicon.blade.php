@props([
    'icon' => '',
    'size' => 'md', // xs, sm, md, lg, xl
    'class' => ''
])

@php
    $sizeClasses = [
        'xs' => 'h-3 w-3',
        'sm' => 'h-4 w-4',
        'md' => 'h-5 w-5',
        'lg' => 'h-6 w-6',
        'xl' => 'h-8 w-8'
    ];

    $iconClass = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

{{ $icon }}

<{{ $tag ?? 'svg' }}
    class="{{ $iconClass }} {{ $class }} {{ $attributes->get('class') ?? '' }}"
    {{ $attributes->except(['class']) }}
>
    {{ $slot }}
</{{ $tag ?? 'svg' }}>