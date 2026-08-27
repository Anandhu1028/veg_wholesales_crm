<x-layouts.app :headerTitle="'Vegetable Catalog & Pricing'" :headerSubtitle="'Manage wholesale produce items, standard rates and stock levels'">
    <div class="p-6 space-y-6">
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Filter Bar -->
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-3">
                <form action="{{ route('products.index') }}" method="GET" class="flex flex-wrap items-center gap-2 flex-1">
                    <div class="relative min-w-[240px] flex-1">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search vegetable by name or SKU..."
                            class="w-full text-xs bg-white border border-slate-200 rounded-lg pl-8 pr-3 py-2 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500"
                        >
                        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <select name="category" class="text-xs bg-white border border-slate-200 rounded-lg py-2 px-3 focus:border-emerald-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                        Filter
                    </button>
                </form>

                <a href="{{ route('products.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Add Vegetable</span>
                </a>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Vegetable</th>
                            <th class="py-3 px-4">Code / SKU</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Unit</th>
                            <th class="py-3 px-4 text-right">Default Wholesale Rate</th>
                            <th class="py-3 px-4 text-right">Physical Stock</th>
                            <th class="py-3 px-4 text-right">Available</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($products as $product)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    {{ $product->name }}
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-500">
                                    {{ $product->code }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[11px] font-medium">
                                        {{ $product->category }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-700">
                                    {{ $product->unit }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-700 text-sm">
                                    ₹{{ number_format($product->base_price, 2) }}/{{ $product->unit }}
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-slate-800">
                                    {{ (int)$product->stock_quantity }} {{ $product->unit }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold {{ $product->isLowStock() ? 'text-rose-600' : 'text-slate-800' }}">
                                    {{ (int)$product->available_stock }} {{ $product->unit }}
                                </td>
                                <td class="py-3 px-4">
                                    <x-status-badge :status="$product->status" size="xs" />
                                </td>
                                <td class="py-3 px-4 text-right space-x-2">
                                    <a href="{{ route('products.edit', $product) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-xs text-slate-400">
                                    No products found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$products" />
        </div>
    </div>
</x-layouts.app>
