<x-layouts.app :headerTitle="'Create Manual Order'" :headerSubtitle="'Place a wholesale vegetable order manually for customer'">
    <div class="p-6 max-w-4xl mx-auto space-y-6" x-data="{
        rows: [
            { product_id: '{{ $products->first()->id ?? '' }}', quantity: 20, unit_price: {{ $products->first()->base_price ?? 40 }}, unit: '{{ $products->first()->unit ?? 'kg' }}' }
        ],
        productsMap: {{ $products->keyBy('id')->toJson() }},
        addRow() {
            const firstId = Object.keys(this.productsMap)[0] || '';
            const prod = this.productsMap[firstId] || {};
            this.rows.push({
                product_id: firstId,
                quantity: 10,
                unit_price: prod.base_price || 0,
                unit: prod.unit || 'kg'
            });
        },
        removeRow(index) {
            if (this.rows.length > 1) {
                this.rows.splice(index, 1);
            }
        },
        onProductChange(index) {
            const row = this.rows[index];
            const prod = this.productsMap[row.product_id];
            if (prod) {
                row.unit_price = prod.base_price;
                row.unit = prod.unit;
            }
        },
        calculateSubtotal() {
            return this.rows.reduce((sum, r) => sum + (parseFloat(r.quantity || 0) * parseFloat(r.unit_price || 0)), 0);
        }
    }">
        <form action="{{ route('orders.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Customer & Delivery Settings -->
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Customer & Dispatch Details</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Customer / Business <span class="text-rose-500">*</span></label>
                        <select name="customer_id" required class="w-full text-xs rounded-lg border-slate-300 focus:border-emerald-500 py-2">
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->displayName }} ({{ $c->whatsapp_number }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Delivery Date</label>
                        <input type="date" name="delivery_date" value="{{ now()->addDay()->toDateString() }}" class="w-full text-xs rounded-lg border-slate-300 focus:border-emerald-500 py-2">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Time Slot</label>
                        <select name="time_slot" class="w-full text-xs rounded-lg border-slate-300 focus:border-emerald-500 py-2">
                            <option value="Morning (6:00 AM - 9:00 AM)">Morning (6:00 AM - 9:00 AM)</option>
                            <option value="Afternoon (12:00 PM - 3:00 PM)">Afternoon (12:00 PM - 3:00 PM)</option>
                            <option value="Evening (5:00 PM - 8:00 PM)">Evening (5:00 PM - 8:00 PM)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full text-xs rounded-lg border-slate-300 focus:border-emerald-500 py-2">
                            <option value="Cash on Delivery">Cash on Delivery</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Credit / Net 30">Credit / Net 30</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Vegetable Order Line Items -->
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="text-sm font-bold text-slate-900">Order Items</h3>
                    <button type="button" @click="addRow()" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                        + Add Vegetable
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(row, idx) in rows" :key="idx">
                        <div class="flex flex-wrap md:flex-nowrap items-center gap-3 p-3 bg-slate-50 rounded-lg border border-slate-200/70">
                            <!-- Product Select -->
                            <div class="flex-1 min-w-[180px]">
                                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Product</label>
                                <select
                                    :name="`items[${idx}][product_id]`"
                                    x-model="row.product_id"
                                    @change="onProductChange(idx)"
                                    required
                                    class="w-full text-xs bg-white rounded-md border-slate-300 py-1.5"
                                >
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} (Stock: {{ (int)$p->stock_quantity }} {{ $p->unit }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Quantity -->
                            <div class="w-24">
                                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Qty</label>
                                <input
                                    type="number"
                                    step="0.5"
                                    :name="`items[${idx}][quantity]`"
                                    x-model="row.quantity"
                                    required
                                    class="w-full text-xs bg-white rounded-md border-slate-300 py-1.5 px-2"
                                >
                            </div>

                            <!-- Unit Rate -->
                            <div class="w-28">
                                <label class="block text-[10px] font-semibold text-slate-500 mb-1">Rate (₹/<span x-text="row.unit"></span>)</label>
                                <input
                                    type="number"
                                    step="0.5"
                                    :name="`items[${idx}][unit_price]`"
                                    x-model="row.unit_price"
                                    required
                                    class="w-full text-xs bg-white rounded-md border-slate-300 py-1.5 px-2 font-mono"
                                >
                            </div>

                            <!-- Line Total -->
                            <div class="w-28 text-right">
                                <span class="block text-[10px] font-semibold text-slate-500 mb-1">Subtotal</span>
                                <span class="text-xs font-bold text-slate-900" x-text="'₹' + ((row.quantity || 0) * (row.unit_price || 0)).toFixed(2)"></span>
                            </div>

                            <!-- Remove Row -->
                            <div class="pt-4">
                                <button type="button" @click="removeRow(idx)" class="text-slate-400 hover:text-rose-500 p-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Total Amount Summary -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-sm">
                    <span class="font-semibold text-slate-600">Calculated Grand Total:</span>
                    <span class="text-lg font-extrabold text-emerald-700 font-mono" x-text="'₹' + calculateSubtotal().toFixed(2)"></span>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('orders.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-colors">
                    Create & Confirm Order
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
