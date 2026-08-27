@php
    $currentRoute = request()->route() ? request()->route()->getName() : '';
    $unreadCount = \App\Models\Conversation::sum('unread_count');
@endphp

<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    class="fixed inset-y-0 left-0 z-40 w-64 bg-[#09131f] text-slate-300 flex flex-col transition-transform duration-200 ease-in-out border-r border-slate-800 shrink-0"
>
    <!-- Brand Header -->
    <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800/80 bg-[#070f1a]">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-emerald-400 flex items-center justify-center text-white shadow-sm shadow-emerald-500/20 ring-1 ring-white/10">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <div>
                <h1 class="text-base font-bold text-white tracking-tight flex items-center gap-1.5">
                    FreshDeal
                    <span class="text-[10px] uppercase font-semibold bg-emerald-500/20 text-emerald-400 px-1.5 py-0.5 rounded">Pro</span>
                </h1>
                <p class="text-[11px] text-slate-400 font-medium">Wholesale Vegetables</p>
            </div>
        </a>
        <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <!-- Navigation List -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scrollbar">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition-all {{ str_starts_with($currentRoute, 'dashboard') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
            <svg class="w-4 h-4 {{ str_starts_with($currentRoute, 'dashboard') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            <span>Dashboard</span>
        </a>

        <!-- Inbox -->
        <a href="{{ route('inbox') }}" class="flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-lg transition-all {{ str_starts_with($currentRoute, 'inbox') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 {{ str_starts_with($currentRoute, 'inbox') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span>Inbox</span>
            </div>
            @if($unreadCount > 0)
                <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ str_starts_with($currentRoute, 'inbox') ? 'bg-white text-emerald-700' : 'bg-emerald-500 text-white' }} animate-pulse">
                    {{ $unreadCount }}
                </span>
            @endif
        </a>

        <!-- Orders -->
        <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition-all {{ str_starts_with($currentRoute, 'orders') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
            <svg class="w-4 h-4 {{ str_starts_with($currentRoute, 'orders') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            <span>Orders</span>
        </a>

        <!-- Customers -->
        <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition-all {{ str_starts_with($currentRoute, 'customers') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
            <svg class="w-4 h-4 {{ str_starts_with($currentRoute, 'customers') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span>Customers</span>
        </a>

        <!-- Products -->
        <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition-all {{ str_starts_with($currentRoute, 'products') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
            <svg class="w-4 h-4 {{ str_starts_with($currentRoute, 'products') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <span>Products</span>
        </a>

        <!-- Inventory -->
        <a href="{{ route('inventory.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition-all {{ str_starts_with($currentRoute, 'inventory') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
            <svg class="w-4 h-4 {{ str_starts_with($currentRoute, 'inventory') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span>Inventory</span>
        </a>

        <!-- Suppliers -->
        <a href="{{ route('suppliers.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition-all {{ str_starts_with($currentRoute, 'suppliers') || str_starts_with($currentRoute, 'purchases') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
            <svg class="w-4 h-4 {{ str_starts_with($currentRoute, 'suppliers') || str_starts_with($currentRoute, 'purchases') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span>Suppliers & Purchases</span>
        </a>

        <!-- Deliveries -->
        <a href="{{ route('deliveries.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition-all {{ str_starts_with($currentRoute, 'deliveries') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
            <svg class="w-4 h-4 {{ str_starts_with($currentRoute, 'deliveries') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
            <span>Deliveries</span>
        </a>

        <!-- Payments -->
        <a href="{{ route('payments.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition-all {{ str_starts_with($currentRoute, 'payments') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
            <svg class="w-4 h-4 {{ str_starts_with($currentRoute, 'payments') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span>Payments</span>
        </a>

        <!-- Reports -->
        <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition-all {{ str_starts_with($currentRoute, 'reports') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
            <svg class="w-4 h-4 {{ str_starts_with($currentRoute, 'reports') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span>Reports</span>
        </a>

        <!-- WhatsApp Settings -->
        <a href="{{ route('whatsapp.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition-all {{ str_starts_with($currentRoute, 'whatsapp') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
            <svg class="w-4 h-4 {{ str_starts_with($currentRoute, 'whatsapp') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <span>WhatsApp</span>
        </a>

        <!-- Staff -->
        <a href="{{ route('staff.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-semibold rounded-lg transition-all {{ str_starts_with($currentRoute, 'staff') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
            <svg class="w-4 h-4 {{ str_starts_with($currentRoute, 'staff') ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <span>Staff</span>
        </a>
    </nav>

    <!-- Connected WhatsApp Box -->
    <div class="px-3 py-3 border-t border-slate-800/80 bg-[#070f1a]">
        <div class="bg-[#112233] rounded-xl p-3 border border-slate-700/60 shadow-xs">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Connected WhatsApp</span>
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-[#25D366]/20 text-[#25D366] flex items-center justify-center font-bold text-xs shrink-0">
                    WA 1
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-white truncate">+971 55 125 4003</p>
                    <p class="text-[10px] text-emerald-400 font-medium">Demo Connected</p>
                </div>
            </div>
        </div>
    </div>

    <!-- User Profile & Logout -->
    <div class="p-3 border-t border-slate-800 bg-[#050b14] flex items-center justify-between">
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-8 h-8 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center ring-2 ring-emerald-500/30 shrink-0">
                {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name ?? 'Anandhu' }}</p>
                <p class="text-[10px] text-slate-400 capitalize truncate">{{ str_replace('_', ' ', auth()->user()->role ?? 'Administrator') }}</p>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" title="Logout" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </button>
        </form>
    </div>
</aside>
