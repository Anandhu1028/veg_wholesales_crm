@props([
    'status' => 'New',
    'size' => 'sm',
])

@php
    $statusLower = strtolower(str_replace([' ', '_'], '', $status));

    $colors = match($statusLower) {
        'new', 'pending', 'waiting', 'draft' => 'bg-amber-50 text-amber-700 border-amber-200 ring-amber-500/10',
        'confirmed', 'ready', 'active', 'connected', 'botactive' => 'bg-emerald-50 text-emerald-700 border-emerald-200 ring-emerald-500/10',
        'processing', 'ordered' => 'bg-blue-50 text-blue-700 border-blue-200 ring-blue-500/10',
        'outfordelivery' => 'bg-indigo-50 text-indigo-700 border-indigo-200 ring-indigo-500/10',
        'delivered', 'completed', 'paid', 'received' => 'bg-emerald-100 text-emerald-800 border-emerald-300 ring-emerald-600/10',
        'partiallypaid' => 'bg-orange-50 text-orange-700 border-orange-200 ring-orange-500/10',
        'unpaid', 'overdue', 'cancelled', 'failed', 'inactive', 'blocked' => 'bg-rose-50 text-rose-700 border-rose-200 ring-rose-500/10',
        'humanrequired', 'human' => 'bg-purple-50 text-purple-700 border-purple-200 ring-purple-500/10',
        default => 'bg-slate-100 text-slate-700 border-slate-200 ring-slate-500/10'
    };

    $dotColors = match($statusLower) {
        'new', 'pending', 'waiting', 'draft' => 'bg-amber-500',
        'confirmed', 'ready', 'active', 'connected', 'botactive' => 'bg-emerald-500',
        'processing', 'ordered' => 'bg-blue-500',
        'outfordelivery' => 'bg-indigo-500',
        'delivered', 'completed', 'paid', 'received' => 'bg-emerald-600',
        'partiallypaid' => 'bg-orange-500',
        'unpaid', 'overdue', 'cancelled', 'failed', 'inactive', 'blocked' => 'bg-rose-500',
        'humanrequired', 'human' => 'bg-purple-500',
        default => 'bg-slate-400'
    };

    $sizeClasses = match($size) {
        'xs' => 'text-[10px] px-1.5 py-0.5',
        'sm' => 'text-xs px-2.5 py-1',
        'md' => 'text-sm px-3 py-1.5',
        default => 'text-xs px-2.5 py-1'
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 font-medium rounded-full border ring-1 {$sizeClasses} {$colors}"]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $dotColors }}"></span>
    {{ $status }}
</span>
