@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => null,
    'color' => 'emerald', // emerald, blue, amber, purple, rose
    'trend' => null,
    'trendUp' => true,
])

@php
    $iconBgs = [
        'emerald' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
        'blue' => 'bg-blue-50 text-blue-600 border-blue-200',
        'amber' => 'bg-amber-50 text-amber-600 border-amber-200',
        'purple' => 'bg-purple-50 text-purple-600 border-purple-200',
        'rose' => 'bg-rose-50 text-rose-600 border-rose-200',
    ];
    $iconBg = $iconBgs[$color] ?? $iconBgs['emerald'];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs hover:shadow-sm transition-all duration-200']) }}>
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ $title }}</p>
            <h3 class="mt-2 text-2xl font-bold text-slate-900 tracking-tight">{{ $value }}</h3>
            @if($subtitle)
                <p class="mt-1 text-xs text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
        @if($icon)
            <div class="w-10 h-10 rounded-lg flex items-center justify-center border {{ $iconBg }} shrink-0">
                {!! $icon !!}
            </div>
        @endif
    </div>

    @if($trend)
        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center text-xs">
            <span class="inline-flex items-center font-medium {{ $trendUp ? 'text-emerald-600' : 'text-rose-600' }}">
                @if($trendUp)
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                @else
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"/></svg>
                @endif
                {{ $trend }}
            </span>
            <span class="text-slate-400 ml-1.5">vs last week</span>
        </div>
    @endif
</div>
