<x-layouts.app :headerTitle="'Add New Vegetable Product'" :headerSubtitle="'Catalog new produce item with wholesale rates'">
    <div class="p-6 max-w-2xl mx-auto">
        <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                <h3 class="text-sm font-bold text-slate-900">Product Information</h3>
                <a href="{{ route('products.index') }}" class="text-xs text-slate-500 hover:text-slate-900">&larr; Back to Catalog</a>
            </div>

            <form action="{{ route('products.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Vegetable Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Baby Spinach" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Product Code / SKU <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" value="{{ old('code', 'VEG-' . strtoupper(Str::random(4))) }}" required class="w-full text-xs rounded-lg border-slate-300 py-2 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Category</label>
                        <select name="category" class="w-full text-xs rounded-lg border-slate-300 py-2">
                            <option value="Vegetables">Vegetables</option>
                            <option value="Leafy Greens">Leafy Greens</option>
                            <option value="Exotic Produce">Exotic Produce</option>
                            <option value="Herbs & Spices">Herbs & Spices</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Measurement Unit</label>
                        <select name="unit" class="w-full text-xs rounded-lg border-slate-300 py-2">
                            <option value="kg">kg (Kilogram)</option>
                            <option value="box">box</option>
                            <option value="crate">crate</option>
                            <option value="piece">piece</option>
                            <option value="bag">bag</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Base Selling Price (₹) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.5" name="base_price" value="{{ old('base_price') }}" required placeholder="e.g. 45.00" class="w-full text-xs rounded-lg border-slate-300 py-2 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Cost Price (₹)</label>
                        <input type="number" step="0.5" name="cost_price" value="{{ old('cost_price') }}" placeholder="e.g. 30.00" class="w-full text-xs rounded-lg border-slate-300 py-2 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Initial Stock Quantity</label>
                        <input type="number" step="1" name="stock_quantity" value="{{ old('stock_quantity', 500) }}" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Low Stock Warning Limit</label>
                        <input type="number" step="1" name="low_stock_threshold" value="{{ old('low_stock_threshold', 100) }}" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
                        <select name="status" class="w-full text-xs rounded-lg border-slate-300 py-2">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="out_of_stock">Out of Stock</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('products.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs">
                        Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
