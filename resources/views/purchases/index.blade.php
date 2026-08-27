<x-layouts.app :headerTitle="'Purchase Orders'" :headerSubtitle="'Procure fresh vegetables from growers and update inventory'">
    <div class="p-6 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-stat-card
                title="Total Stock Procured"
                :value="'₹' . number_format($totalPurchases, 0)"
                subtitle="Fulfilled purchase orders"
                color="emerald"
            />
            <x-stat-card
                title="Pending Deliveries"
                :value="$pendingPOs"
                subtitle="Awaiting farm arrival"
                color="amber"
            />
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Filter Bar -->
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <a href="{{ route('purchases.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ !request('status') ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700' }}">
                        All Orders
                    </a>
                    <a href="{{ route('purchases.index', ['status' => 'Ordered']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'Ordered' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700' }}">
                        Ordered
                    </a>
                    <a href="{{ route('purchases.index', ['status' => 'Received']) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request('status') === 'Received' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700' }}">
                        Received
                    </a>
                </div>

                <a href="{{ route('purchases.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs flex items-center gap-1.5">
                    <span>+ New Purchase Order</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">PO #</th>
                            <th class="py-3 px-4">Supplier / Farm</th>
                            <th class="py-3 px-4">Order Date</th>
                            <th class="py-3 px-4">Expected Arrival</th>
                            <th class="py-3 px-4">Produce Items</th>
                            <th class="py-3 px-4 text-right">Total Cost</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($purchases as $po)
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <a href="{{ route('purchases.show', $po) }}" class="text-emerald-600 hover:underline">
                                        #{{ $po->po_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    {{ $po->supplier->company_name }}
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $po->order_date->format('M d, Y') }}
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $po->expected_delivery_date ? $po->expected_delivery_date->format('M d, Y') : 'Immediate' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-slate-800">{{ $po->items->count() }} items</span>
                                    <span class="text-[10px] text-slate-400 block truncate max-w-xs">
                                        {{ $po->items->pluck('product_name')->implode(', ') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900">
                                    ₹{{ number_format($po->total_amount, 2) }}
                                </td>
                                <td class="py-3 px-4">
                                    <x-status-badge :status="$po->status" size="xs" />
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('purchases.show', $po) }}" class="text-xs font-semibold text-emerald-600 hover:underline">
                                        Details &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-xs text-slate-400">No purchase orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$purchases" />
        </div>
    </div>
</x-layouts.app>
