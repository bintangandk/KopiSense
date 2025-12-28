@props([
    'icon' => null,
    'variant' => 'primary',
    'type' => 'button',
])

@php
    $baseClass = 'btn btn-' . $variant;
@endphp

<button {{ $attributes->merge(['type' => $type, 'class' => $baseClass]) }}>
    @if ($icon)
        <i class="{{ $icon }} me-1"></i>
    @endif

    {{ $slot }}
</button>
