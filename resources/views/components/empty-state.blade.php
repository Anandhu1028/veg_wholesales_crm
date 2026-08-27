@props([
    'title' => 'No records found',
    'description' => 'There are no items to display at this time.',
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'text-center py-12 px-4 bg-white rounded-xl border border-slate-200/80 my-4']) }}>
    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
        @if($icon)
            {!! $icon !!}
        @else
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
        @endif
    </div>
    <h3 class="text-sm font-semibold text-slate-900">{{ $title }}</h3>
    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">{{ $description }}</p>
    @if($slot->isNotEmpty())
        <div class="mt-4 flex justify-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
