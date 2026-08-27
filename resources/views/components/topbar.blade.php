@props([
    'title' => 'Dashboard',
    'subtitle' => null,
])

<header class="h-16 bg-white border-b border-slate-200/80 px-6 flex items-center justify-between sticky top-0 z-30">
    <div class="flex items-center gap-4">
        <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-slate-500 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">{{ $title }}</h2>
            @if($subtitle)
                <p class="text-xs text-slate-500 font-medium hidden sm:block">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    <!-- Actions & Global Simulate Message Button -->
    <div class="flex items-center gap-3">
        <!-- Prominent Simulate WhatsApp Message Button -->
        <button
            type="button"
            x-data
            @click="$dispatch('open-modal', 'simulate-whatsapp-modal')"
            class="inline-flex items-center gap-2 px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-[#25D366] hover:bg-[#1faa52] text-white shadow-xs transition-colors cursor-pointer"
        >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
            </svg>
            <span>+ Simulate WhatsApp Message</span>
        </button>

        <!-- Quick Status Indicator -->
        <div class="hidden lg:flex items-center gap-2 pl-3 border-l border-slate-200 text-xs">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="text-slate-600 font-medium">Dubai Produce Hub: <strong class="text-slate-900">Open (04:00 - 22:00)</strong></span>
        </div>
    </div>
</header>
