<x-layouts.app :headerTitle="'Business Analytics & Reports'" :headerSubtitle="'Performance metrics, margins, customer sales rankings, and wastage tracking'">
    <div class="p-6 space-y-6" x-data="{ currentTab: '{{ $tab ?? 'sales' }}' }">
        <!-- Top Metrics Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card
                title="Gross Sales"
                :value="'₹' . number_format($totalSales, 0)"
                subtitle="All fulfilled orders"
                color="emerald"
            />
            <x-stat-card
                title="Produce Procurement"
                :value="'₹' . number_format($totalPurchases, 0)"
                subtitle="Farm purchases cost"
                color="blue"
            />
            <x-stat-card
                title="Estimated Gross Margin"
                :value="'₹' . number_format($grossProfit, 0)"
                subtitle="Revenue minus procurement"
                color="purple"
            />
            <x-stat-card
                title="Gross Margin %"
                :value="$marginPercent . '%'"
                subtitle="Average markup efficiency"
                color="amber"
            />
        </div>

        <!-- Reports Tabs -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="border-b border-slate-200 px-5 flex items-center gap-6 text-xs font-semibold bg-slate-50/50">
                <button
                    @click="currentTab = 'sales'"
                    :class="currentTab === 'sales' ? 'border-emerald-600 text-emerald-700 border-b-2' : 'text-slate-500 hover:text-slate-900'"
                    class="py-3 transition-colors"
                >
                    Daily Sales Trends
                </button>
                <button
                    @click="currentTab = 'customers'"
                    :class="currentTab === 'customers' ? 'border-emerald-600 text-emerald-700 border-b-2' : 'text-slate-500 hover:text-slate-900'"
                    class="py-3 transition-colors"
                >
                    Top Wholesale Customers
                </button>
                <button
                    @click="currentTab = 'products'"
                    :class="currentTab === 'products' ? 'border-emerald-600 text-emerald-700 border-b-2' : 'text-slate-500 hover:text-slate-900'"
                    class="py-3 transition-colors"
                >
                    Top Selling Produce
                </button>
                <button
                    @click="currentTab = 'outstanding'"
                    :class="currentTab === 'outstanding' ? 'border-emerald-600 text-emerald-700 border-b-2' : 'text-slate-500 hover:text-slate-900'"
                    class="py-3 transition-colors"
                >
                    Outstanding Balances
                </button>
                <button
                    @click="currentTab = 'wastage'"
                    :class="currentTab === 'wastage' ? 'border-emerald-600 text-emerald-700 border-b-2' : 'text-slate-500 hover:text-slate-900'"
                    class="py-3 transition-colors"
                >
                    Wastage & Loss Report
                </button>
            </div>

            <!-- Tab 1: Daily Sales -->
            <div x-show="currentTab === 'sales'" class="p-6">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Daily Sales Breakdown (Last 14 Days)</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Date</th>
                                <th class="py-3 px-4 text-center">Orders Count</th>
                                <th class="py-3 px-4 text-right">Daily Revenue Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($dailySales as $ds)
                                <tr>
                                    <td class="py-3 px-4 font-bold text-slate-900">{{ \Carbon\Carbon::parse($ds->date)->format('l, M d, Y') }}</td>
                                    <td class="py-3 px-4 text-center font-semibold text-slate-800">{{ $ds->order_count }}</td>
                                    <td class="py-3 px-4 text-right font-bold text-emerald-700">₹{{ number_format($ds->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-xs text-slate-400">No sales records.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: Top Customers -->
            <div x-show="currentTab === 'customers'" x-cloak class="p-6">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Top Wholesale Buyers by Revenue</h4>
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Customer Name</th>
                            <th class="py-3 px-4">Business Type</th>
                            <th class="py-3 px-4 text-center">Total Orders</th>
                            <th class="py-3 px-4 text-right">Total Lifetime Spent</th>
                            <th class="py-3 px-4 text-right">Current Ledger Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($topCustomers as $tc)
                            <tr>
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $tc->displayName }}</td>
                                <td class="py-3 px-4">{{ $tc->business_type }}</td>
                                <td class="py-3 px-4 text-center font-semibold">{{ $tc->orders_count }}</td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-700">₹{{ number_format($tc->orders_sum_total_amount ?? 0, 2) }}</td>
                                <td class="py-3 px-4 text-right font-bold {{ $tc->outstanding_balance > 0 ? 'text-rose-600' : 'text-slate-500' }}">
                                    ₹{{ number_format($tc->outstanding_balance, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Tab 3: Top Products -->
            <div x-show="currentTab === 'products'" x-cloak class="p-6">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Highest Velocity Vegetables</h4>
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Vegetable</th>
                            <th class="py-3 px-4 text-right">Total Volume Dispatched</th>
                            <th class="py-3 px-4 text-right">Gross Sales Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($topProducts as $tp)
                            <tr>
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $tp->product_name }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-slate-800">{{ number_format($tp->total_qty) }} kg</td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-700">₹{{ number_format($tp->total_revenue, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Tab 4: Outstanding -->
            <div x-show="currentTab === 'outstanding'" x-cloak class="p-6">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Aging Receivables & Outstanding Balances</h4>
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-4">WhatsApp</th>
                            <th class="py-3 px-4 text-right">Credit Limit</th>
                            <th class="py-3 px-4 text-right">Outstanding Amount</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($outstandingCustomers as $oc)
                            <tr>
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $oc->displayName }}</td>
                                <td class="py-3 px-4 font-mono text-slate-600">{{ $oc->whatsapp_number }}</td>
                                <td class="py-3 px-4 text-right text-slate-500">₹{{ number_format($oc->credit_limit, 2) }}</td>
                                <td class="py-3 px-4 text-right font-bold text-rose-600 text-sm">₹{{ number_format($oc->outstanding_balance, 2) }}</td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('customers.show', $oc) }}" class="text-xs font-semibold text-emerald-600 hover:underline">
                                        Collect Payment &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Tab 5: Wastage -->
            <div x-show="currentTab === 'wastage'" x-cloak class="p-6">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Loss & Spoilage History</h4>
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Produce</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4 text-right">Loss Quantity</th>
                            <th class="py-3 px-4">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($wastageLogs as $wl)
                            <tr>
                                <td class="py-3 px-4">{{ $wl->created_at->format('M d, Y') }}</td>
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $wl->product?->name }}</td>
                                <td class="py-3 px-4 capitalize font-semibold text-rose-600">{{ $wl->type }}</td>
                                <td class="py-3 px-4 text-right font-bold text-rose-600">{{ abs((float)$wl->quantity) }} {{ $wl->product?->unit }}</td>
                                <td class="py-3 px-4 text-slate-500">{{ $wl->notes ?: 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-xs text-slate-400">No wastage entries logged.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
