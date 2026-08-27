<x-layouts.app :headerTitle="'Inventory & Cold Room Stock'" :headerSubtitle="'Real-time vegetable stock levels, reserved produce, and ledger audit'">
    <div class="p-6 space-y-6" x-data="{ adjustModalOpen: false, selectedProductId: '{{ $products->first()->id ?? '' }}' }">
        <!-- Top Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-stat-card
                title="Total Stock Volume"
                :value="number_format($totalStockKg, 0) . ' kg'"
                subtitle="In storage & packing warehouse"
                color="emerald"
            />
            <x-stat-card
                title="Low Stock Items"
                :value="$lowStockCount"
                subtitle="Below threshold warning"
                color="rose"
            />
            <x-stat-card
                title="Catalog Items"
                :value="$totalItems"
                subtitle="Active tracked SKUs"
                color="blue"
            />
        </div>

        <!-- Inventory Balance Table -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Current Stock Balances</h3>
                    <p class="text-xs text-slate-500">Produce available for WhatsApp order matching</p>
                </div>

                <!-- Stock Adjustment Trigger -->
                <button
                    type="button"
                    @click="$dispatch('open-modal', 'adjust-stock-modal')"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>Record Stock Movement</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Vegetable</th>
                            <th class="py-3 px-4">SKU</th>
                            <th class="py-3 px-4 text-right">Physical Stock</th>
                            <th class="py-3 px-4 text-right">Reserved (In Orders)</th>
                            <th class="py-3 px-4 text-right">Available for Sale</th>
                            <th class="py-3 px-4 text-right">Low Stock Alert</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($products as $product)
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    {{ $product->name }}
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-400">
                                    {{ $product->code }}
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-slate-800">
                                    {{ (int)$product->stock_quantity }} {{ $product->unit }}
                                </td>
                                <td class="py-3 px-4 text-right text-amber-600 font-semibold">
                                    {{ (int)$product->reserved_quantity }} {{ $product->unit }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold {{ $product->isLowStock() ? 'text-rose-600' : 'text-emerald-700' }} text-sm">
                                    {{ (int)$product->available_stock }} {{ $product->unit }}
                                </td>
                                <td class="py-3 px-4 text-right text-slate-500">
                                    {{ (int)$product->low_stock_threshold }} {{ $product->unit }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($product->isLowStock())
                                        <span class="text-[10px] font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full">
                                            ⚠️ Low Stock
                                        </span>
                                    @else
                                        <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                            ✓ Healthy
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Inventory Transaction Log -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-900">Stock Movement Audit Trail</h3>
                <p class="text-xs text-slate-500">Log of purchases, dispatches, wastage, damage and physical adjustments</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Date & Time</th>
                            <th class="py-3 px-4">Vegetable</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4 text-right">Quantity Change</th>
                            <th class="py-3 px-4 text-right">Balance After</th>
                            <th class="py-3 px-4">Notes / Reference</th>
                            <th class="py-3 px-4">Logged By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($transactions as $t)
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-3 px-4 text-[11px] text-slate-400 whitespace-nowrap">
                                    {{ $t->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    {{ $t->product?->name }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="capitalize px-2 py-0.5 rounded text-[10px] font-bold {{ $t->quantity > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                        {{ str_replace('_', ' ', $t->type) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-bold {{ $t->quantity > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $t->quantity > 0 ? '+' : '' }}{{ (float)$t->quantity }} {{ $t->product?->unit }}
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-slate-800">
                                    {{ (float)$t->balance_after }} {{ $t->product?->unit }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 max-w-xs truncate">
                                    {{ $t->notes ?: ($t->reference_type ? $t->reference_type . ' #' . $t->reference_id : 'Manual update') }}
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $t->creator?->name ?? 'System Bot' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-xs text-slate-400">No stock transactions logged yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$transactions" />
        </div>
    </div>

    <!-- Stock Movement Modal -->
    <x-modal name="adjust-stock-modal" title="Record Stock Movement" maxWidth="md">
        <form action="{{ route('inventory.adjust') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Select Produce</label>
                <select name="product_id" required class="w-full text-xs rounded-lg border-slate-300 py-2">
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} (Current: {{ (int)$p->stock_quantity }} {{ $p->unit }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Movement Type</label>
                <select name="type" required class="w-full text-xs rounded-lg border-slate-300 py-2">
                    <option value="stock_in">Stock In (Fresh Harvest Arrival)</option>
                    <option value="wastage">Wastage / Spoiled</option>
                    <option value="damage">Damage in Transit</option>
                    <option value="adjustment_in">Manual Adjustment (Add)</option>
                    <option value="adjustment_out">Manual Adjustment (Deduct)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Quantity</label>
                <input type="number" step="0.5" name="quantity" required placeholder="e.g. 50" class="w-full text-xs rounded-lg border-slate-300 py-2">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Reason / Notes</label>
                <textarea name="notes" rows="2" placeholder="e.g. Physical inventory check difference" class="w-full text-xs rounded-lg border-slate-300 p-2"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" @click="$dispatch('close-modal', 'adjust-stock-modal')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs">
                    Save Stock Entry
                </button>
            </div>
        </form>
    </x-modal>
</x-layouts.app>
