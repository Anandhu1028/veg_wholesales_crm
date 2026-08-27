<x-layouts.app :headerTitle="'Suppliers & Purchase Management'" :headerSubtitle="'Farms, growers and agricultural importers'">
    <div class="p-6 space-y-6">
        <!-- Top Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-stat-card
                title="Active Suppliers"
                :value="$totalSuppliers"
                subtitle="Farms & wholesale growers"
                color="blue"
            />
            <x-stat-card
                title="Total Payable"
                :value="'₹' . number_format($totalPayable, 0)"
                subtitle="Outstanding supplier invoices"
                color="rose"
            />
            <div class="bg-emerald-600 text-white rounded-xl p-5 shadow-xs flex items-center justify-between">
                <div>
                    <h4 class="text-xs uppercase font-bold text-emerald-100">Procurement Actions</h4>
                    <p class="text-sm font-bold text-white mt-1">Purchase Orders Hub</p>
                </div>
                <a href="{{ route('purchases.index') }}" class="px-3.5 py-1.5 bg-white text-emerald-800 hover:bg-emerald-50 rounded-lg text-xs font-semibold shadow-xs">
                    View Purchases &rarr;
                </a>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Filter Bar -->
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between gap-4">
                <form action="{{ route('suppliers.index') }}" method="GET" class="relative max-w-sm flex-1">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search supplier or farm company..."
                        class="w-full text-xs bg-white border border-slate-200 rounded-lg pl-8 pr-3 py-2 text-slate-800 placeholder-slate-400 focus:border-emerald-500"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </form>

                <div class="flex items-center gap-2">
                    <a href="{{ route('purchases.create') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold">
                        + New Purchase Order
                    </a>
                    <a href="{{ route('suppliers.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs flex items-center gap-1.5">
                        <span>+ Add Supplier</span>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Company / Farm</th>
                            <th class="py-3 px-4">Contact Person</th>
                            <th class="py-3 px-4">Phone / WhatsApp</th>
                            <th class="py-3 px-4">Location</th>
                            <th class="py-3 px-4">Payment Terms</th>
                            <th class="py-3 px-4 text-right">Outstanding Balance</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($suppliers as $sup)
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <a href="{{ route('suppliers.show', $sup) }}" class="text-slate-900 hover:text-emerald-600">
                                        {{ $sup->company_name }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-800">
                                    {{ $sup->name }}
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600">
                                    {{ $sup->phone }}
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    {{ $sup->address }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[11px] font-semibold">
                                        {{ $sup->payment_terms }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900">
                                    ₹{{ number_format($sup->outstanding_balance, 2) }}
                                </td>
                                <td class="py-3 px-4">
                                    <x-status-badge :status="$sup->status" size="xs" />
                                </td>
                                <td class="py-3 px-4 text-right space-x-2">
                                    <a href="{{ route('suppliers.show', $sup) }}" class="text-xs font-semibold text-emerald-600 hover:underline">
                                        History &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-xs text-slate-400">No suppliers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$suppliers" />
        </div>
    </div>
</x-layouts.app>
