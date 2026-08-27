<x-layouts.app :headerTitle="'PO #' . $purchase->po_number" :headerSubtitle="'Purchase order details and warehouse receipt'">
    <div class="p-6 space-y-6">
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('purchases.index') }}" class="p-2 rounded-lg border border-slate-200 text-slate-500 hover:text-slate-900">&larr;</a>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-slate-900">#{{ $purchase->po_number }}</h2>
                        <x-status-badge :status="$purchase->status" size="sm" />
                    </div>
                    <p class="text-xs text-slate-500">Supplier: {{ $purchase->supplier->company_name }} • Ordered: {{ $purchase->order_date->format('M d, Y') }}</p>
                </div>
            </div>

            <!-- Status Changer -->
            <form action="{{ route('purchases.update-status', $purchase) }}" method="POST" class="flex items-center gap-2">
                @csrf
                <label class="text-xs font-semibold text-slate-600">PO Status:</label>
                <select name="status" onchange="this.form.submit()" class="text-xs font-semibold rounded-lg border-slate-300 py-1.5 px-3">
                    <option value="Draft" {{ $purchase->status === 'Draft' ? 'selected' : '' }}>Draft</option>
                    <option value="Ordered" {{ $purchase->status === 'Ordered' ? 'selected' : '' }}>Ordered (In Transit)</option>
                    <option value="Received" {{ $purchase->status === 'Received' ? 'selected' : '' }}>Received (Auto-updates Stock)</option>
                    <option value="Cancelled" {{ $purchase->status === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </form>
        </div>

        @if($purchase->status === 'Received')
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-900 font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Stock has been automatically credited to the cold room inventory on {{ $purchase->received_date ? $purchase->received_date->format('M d, Y') : 'receipt' }}.</span>
            </div>
        @endif

        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Procured Produce Items</h3>
                <span class="text-xs text-slate-500">{{ $purchase->items->count() }} items</span>
            </div>

            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Produce Name</th>
                        <th class="py-3 px-4">Quantity</th>
                        <th class="py-3 px-4 text-right">Cost Rate</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($purchase->items as $item)
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $item->product_name }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ (float)$item->quantity }} {{ $item->unit }}</td>
                            <td class="py-3 px-4 text-right text-slate-700">₹{{ number_format($item->unit_price, 2) }}/{{ $item->unit }}</td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900">₹{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-between text-sm font-extrabold text-slate-900">
                <span>Total PO Amount:</span>
                <span class="text-slate-900">₹{{ number_format($purchase->total_amount, 2) }}</span>
            </div>
        </div>
    </div>
</x-layouts.app>
