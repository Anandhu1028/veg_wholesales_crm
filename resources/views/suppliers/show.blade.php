<x-layouts.app :headerTitle="$supplier->company_name" :headerSubtitle="'Supplier ledger and purchase order history'">
    <div class="p-6 space-y-6">
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900">{{ $supplier->company_name }}</h2>
                <p class="text-xs text-slate-500">Contact: {{ $supplier->name }} • {{ $supplier->phone }} • {{ $supplier->address }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-slate-500">Payable Balance</p>
                <p class="text-lg font-bold text-rose-600">₹{{ number_format($supplier->outstanding_balance, 2) }}</p>
            </div>
        </div>

        <!-- Purchase Orders History -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Purchase Orders</h3>
                <a href="{{ route('purchases.create') }}" class="text-xs font-semibold text-emerald-600 hover:underline">+ New PO</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">PO #</th>
                            <th class="py-3 px-4">Order Date</th>
                            <th class="py-3 px-4">Expected Date</th>
                            <th class="py-3 px-4 text-right">Amount</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($supplier->purchaseOrders as $po)
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <a href="{{ route('purchases.show', $po) }}" class="text-emerald-600 hover:underline">
                                        #{{ $po->po_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">{{ $po->order_date->format('M d, Y') }}</td>
                                <td class="py-3 px-4">{{ $po->expected_delivery_date ? $po->expected_delivery_date->format('M d, Y') : 'N/A' }}</td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900">₹{{ number_format($po->total_amount, 2) }}</td>
                                <td class="py-3 px-4"><x-status-badge :status="$po->status" size="xs" /></td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('purchases.show', $po) }}" class="text-xs font-semibold text-emerald-600 hover:underline">
                                        View &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-xs text-slate-400">No purchase orders found for this supplier.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
