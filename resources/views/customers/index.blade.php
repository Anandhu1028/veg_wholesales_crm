<x-layouts.app :headerTitle="'Customer Management'" :headerSubtitle="'Wholesale buyers, hotels, restaurants and supermarkets'">
    <div class="p-6 space-y-6">
        <!-- Top Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-stat-card
                title="Total Customers"
                :value="$totalCustomers"
                subtitle="Registered wholesale buyers"
                color="blue"
                :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z\'/></svg>'"
            />
            <x-stat-card
                title="Active Accounts"
                :value="$activeCustomers"
                subtitle="Regular ordering clients"
                color="emerald"
                :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z\'/></svg>'"
            />
            <x-stat-card
                title="Total Outstanding"
                :value="'₹' . number_format($totalOutstanding, 0)"
                subtitle="Receivable across all ledgers"
                color="rose"
                :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z\'/></svg>'"
            />
        </div>

        <!-- Customer List Container -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Filter Bar -->
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-3">
                <form action="{{ route('customers.index') }}" method="GET" class="flex flex-wrap items-center gap-2 flex-1">
                    <div class="relative min-w-[240px] flex-1">
                        <input
                            type="text"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Search by customer name, business, WhatsApp..."
                            class="w-full text-xs bg-white border border-slate-200 rounded-lg pl-8 pr-3 py-2 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500"
                        >
                        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <select name="business_type" class="text-xs bg-white border border-slate-200 rounded-lg py-2 px-3 focus:border-emerald-500">
                        <option value="">All Business Types</option>
                        <option value="Wholesale" {{ ($filters['business_type'] ?? '') === 'Wholesale' ? 'selected' : '' }}>Wholesale</option>
                        <option value="Hotel" {{ ($filters['business_type'] ?? '') === 'Hotel' ? 'selected' : '' }}>Hotel</option>
                        <option value="Restaurant" {{ ($filters['business_type'] ?? '') === 'Restaurant' ? 'selected' : '' }}>Restaurant</option>
                        <option value="Supermarket" {{ ($filters['business_type'] ?? '') === 'Supermarket' ? 'selected' : '' }}>Supermarket</option>
                        <option value="Retailer" {{ ($filters['business_type'] ?? '') === 'Retailer' ? 'selected' : '' }}>Retailer</option>
                    </select>

                    <button type="submit" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                        Filter
                    </button>
                </form>

                <a href="{{ route('customers.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Add Customer</span>
                </a>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-4">Business</th>
                            <th class="py-3 px-4">WhatsApp</th>
                            <th class="py-3 px-4 text-center">Orders</th>
                            <th class="py-3 px-4 text-right">Outstanding</th>
                            <th class="py-3 px-4">Last Order</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($customers as $customer)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <div class="flex items-center gap-2.5">
                                        <x-customer-avatar :name="$customer->displayName" size="sm" />
                                        <div>
                                            <a href="{{ route('customers.show', $customer) }}" class="text-slate-900 hover:text-emerald-600 font-bold block">
                                                {{ $customer->displayName }}
                                            </a>
                                            <span class="text-[11px] text-slate-400 font-normal">{{ $customer->name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-700">
                                    {{ $customer->business_type }}
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-700">
                                    {{ $customer->whatsapp_number }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-800">
                                    {{ $customer->orders_count }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold {{ $customer->outstanding_balance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    ₹{{ number_format($customer->outstanding_balance, 2) }}
                                </td>
                                <td class="py-3 px-4 text-[11px] text-slate-500 whitespace-nowrap">
                                    {{ $customer->latestOrder ? $customer->latestOrder->created_at->format('M d, Y') : 'No orders yet' }}
                                </td>
                                <td class="py-3 px-4">
                                    <x-status-badge :status="$customer->status" size="xs" />
                                </td>
                                <td class="py-3 px-4 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('customers.show', $customer) }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800">
                                        View &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-xs text-slate-400">
                                    No customers found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$customers" />
        </div>
    </div>
</x-layouts.app>
