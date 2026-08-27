@props([
    'variant' => 'primary', // primary, secondary, outline, danger, whatsapp
    'size' => 'md', // sm, md, lg
    'type' => 'button',
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed select-none';

    $variants = [
        'primary' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs focus:ring-emerald-500',
        'secondary' => 'bg-slate-100 hover:bg-slate-200 text-slate-700 focus:ring-slate-400',
        'outline' => 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 shadow-xs focus:ring-emerald-500',
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-xs focus:ring-rose-500',
        'whatsapp' => 'bg-[#25D366] hover:bg-[#20ba59] text-white shadow-xs focus:ring-emerald-500',
    ];

    $sizes = [
        'sm' => 'text-xs px-2.5 py-1.5 gap-1.5',
        'md' => 'text-sm px-4 py-2 gap-2',
        'lg' => 'text-base px-5 py-2.5 gap-2.5',
    ];

    $variantClass = $variants[$variant] ?? $variants['primary'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "{$baseClasses} {$variantClass} {$sizeClass}"]) }}>
    {{ $slot }}
</button>
