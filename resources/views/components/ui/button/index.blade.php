@props([
    'icon' => null,
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
])

@php
    $baseClass = 'btn btn-' . $variant;
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClass]) }}>
        @if ($icon)
            <i class="{{ $icon }} me-1"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClass]) }}>
        @if ($icon)
            <i class="{{ $icon }} me-1"></i>
        @endif
        {{ $slot }}
    </button>
@endif
