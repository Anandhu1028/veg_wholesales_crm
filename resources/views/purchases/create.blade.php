<x-layouts.app :headerTitle="'Create Purchase Order'" :headerSubtitle="'Order vegetable stock from supplier farms'">
    <div class="p-6 max-w-4xl mx-auto" x-data="{
        rows: [
            { product_id: '{{ $products->first()->id ?? '' }}', quantity: 500, unit_price: {{ $products->first()->cost_price ?? 20 }}, unit: '{{ $products->first()->unit ?? 'kg' }}' }
        ],
        productsMap: {{ $products->keyBy('id')->toJson() }},
        addRow() {
            const firstId = Object.keys(this.productsMap)[0] || '';
            const prod = this.productsMap[firstId] || {};
            this.rows.push({
                product_id: firstId,
                quantity: 200,
                unit_price: prod.cost_price || 20,
                unit: prod.unit || 'kg'
            });
        },
        removeRow(index) {
            if (this.rows.length > 1) this.rows.splice(index, 1);
        },
        onProductChange(index) {
            const row = this.rows[index];
            const prod = this.productsMap[row.product_id];
            if (prod) {
                row.unit_price = prod.cost_price || 0;
                row.unit = prod.unit;
            }
        },
        calculateTotal() {
            return this.rows.reduce((sum, r) => sum + (parseFloat(r.quantity || 0) * parseFloat(r.unit_price || 0)), 0);
        }
    }">
        <form action="{{ route('purchases.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Supplier & Schedule</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Supplier Farm <span class="text-rose-500">*</span></label>
                        <select name="supplier_id" required class="w-full text-xs rounded-lg border-slate-300 py-2">
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->company_name }} ({{ $s->name }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Order Date</label>
                        <input type="date" name="order_date" value="{{ now()->toDateString() }}" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Expected Delivery Date</label>
                        <input type="date" name="expected_delivery_date" value="{{ now()->addDay()->toDateString() }}" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>
                </div>
            </div>

            <!-- Produce Items -->
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="text-sm font-bold text-slate-900">Procurement Items</h3>
                    <button type="button" @click="addRow()" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                        + Add Produce
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(row, idx) in rows" :key="idx">
                        <div class="flex flex-wrap md:flex-nowrap items-center gap-3 p-3 bg-slate-50 rounded-lg border border-slate-200/70">
                            <div class="flex-1 min-w-[180px]">
                                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Produce</label>
                                <select
                                    :name="`items[${idx}][product_id]`"
                                    x-model="row.product_id"
                                    @change="onProductChange(idx)"
                                    required
                                    class="w-full text-xs bg-white rounded-md border-slate-300 py-1.5"
                                >
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="w-28">
                                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Quantity</label>
                                <input
                                    type="number"
                                    step="1"
                                    :name="`items[${idx}][quantity]`"
                                    x-model="row.quantity"
                                    required
                                    class="w-full text-xs bg-white rounded-md border-slate-300 py-1.5 px-2"
                                >
                            </div>

                            <div class="w-32">
                                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Cost Rate (₹/<span x-text="row.unit"></span>)</label>
                                <input
                                    type="number"
                                    step="0.5"
                                    :name="`items[${idx}][unit_price]`"
                                    x-model="row.unit_price"
                                    required
                                    class="w-full text-xs bg-white rounded-md border-slate-300 py-1.5 px-2 font-mono"
                                >
                            </div>

                            <div class="w-28 text-right">
                                <span class="block text-[10px] font-semibold text-slate-500 mb-1">Total</span>
                                <span class="text-xs font-bold text-slate-900" x-text="'₹' + ((row.quantity || 0) * (row.unit_price || 0)).toFixed(2)"></span>
                            </div>

                            <div class="pt-4">
                                <button type="button" @click="removeRow(idx)" class="text-slate-400 hover:text-rose-500 p-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-sm">
                    <span class="font-semibold text-slate-600">Total Purchase Cost:</span>
                    <span class="text-lg font-extrabold text-slate-900 font-mono" x-text="'₹' + calculateTotal().toFixed(2)"></span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('purchases.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs">
                    Issue Purchase Order
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
