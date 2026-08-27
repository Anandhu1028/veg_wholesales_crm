@props([
    'name' => 'Customer',
    'size' => 'md', // sm, md, lg
    'color' => 'emerald',
])

@php
    $sizeClasses = match($size) {
        'xs' => 'w-6 h-6 text-[10px]',
        'sm' => 'w-8 h-8 text-xs',
        'md' => 'w-10 h-10 text-sm font-semibold',
        'lg' => 'w-12 h-12 text-base font-bold',
        'xl' => 'w-16 h-16 text-xl font-bold',
        default => 'w-10 h-10 text-sm font-semibold'
    };

    $colors = [
        'bg-emerald-100 text-emerald-800 border-emerald-200',
        'bg-blue-100 text-blue-800 border-blue-200',
        'bg-amber-100 text-amber-800 border-amber-200',
        'bg-indigo-100 text-indigo-800 border-indigo-200',
        'bg-teal-100 text-teal-800 border-teal-200',
        'bg-rose-100 text-rose-800 border-rose-200',
        'bg-purple-100 text-purple-800 border-purple-200',
    ];
    $idx = abs(crc32($name)) % count($colors);
    $colorClass = $colors[$idx];

    $words = explode(' ', trim($name));
    $initials = '';
    if (count($words) >= 2) {
        $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    } else {
        $initials = strtoupper(substr($name, 0, 2));
    }
@endphp

<div {{ $attributes->merge(['class' => "rounded-full flex items-center justify-center border select-none shrink-0 {$sizeClasses} {$colorClass}"]) }}>
    {{ $initials }}
</div>
