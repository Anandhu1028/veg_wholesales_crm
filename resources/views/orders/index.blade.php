<x-layouts.app :headerTitle="'Order Management'" :headerSubtitle="'Track, fulfill and manage wholesale produce orders'">
    <div class="p-6 space-y-6">
        <!-- Status Tabs & Header Actions -->
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Status Tabs -->
            <div class="flex items-center gap-1 overflow-x-auto pb-1 text-xs font-semibold">
                <a href="{{ route('orders.index', array_merge($filters, ['status' => 'all'])) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ ($filters['status'] ?? 'all') === 'all' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    All ({{ $statusCounts['all'] }})
                </a>
                <a href="{{ route('orders.index', array_merge($filters, ['status' => 'New'])) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ ($filters['status'] ?? '') === 'New' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    New ({{ $statusCounts['New'] }})
                </a>
                <a href="{{ route('orders.index', array_merge($filters, ['status' => 'Confirmed'])) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ ($filters['status'] ?? '') === 'Confirmed' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    Confirmed ({{ $statusCounts['Confirmed'] }})
                </a>
                <a href="{{ route('orders.index', array_merge($filters, ['status' => 'Processing'])) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ ($filters['status'] ?? '') === 'Processing' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    Processing ({{ $statusCounts['Processing'] }})
                </a>
                <a href="{{ route('orders.index', array_merge($filters, ['status' => 'Out for Delivery'])) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ ($filters['status'] ?? '') === 'Out for Delivery' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    Out for Delivery ({{ $statusCounts['Out for Delivery'] }})
                </a>
                <a href="{{ route('orders.index', array_merge($filters, ['status' => 'Delivered'])) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ ($filters['status'] ?? '') === 'Delivered' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    Delivered ({{ $statusCounts['Delivered'] }})
                </a>
            </div>

            <!-- Create Order Button -->
            <a href="{{ route('orders.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs flex items-center justify-center gap-1.5 transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Create Manual Order</span>
            </a>
        </div>

        <!-- Orders Table List -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <!-- Filter Bar -->
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-3">
                <form action="{{ route('orders.index') }}" method="GET" class="flex flex-wrap items-center gap-2 flex-1">
                    <input type="hidden" name="status" value="{{ $filters['status'] ?? 'all' }}">
                    <div class="relative min-w-[240px] flex-1">
                        <input
                            type="text"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Search by order #, customer, phone..."
                            class="w-full text-xs bg-white border border-slate-200 rounded-lg pl-8 pr-3 py-2 text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500"
                        >
                        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <select name="source" class="text-xs bg-white border border-slate-200 rounded-lg py-2 px-3 focus:border-emerald-500">
                        <option value="">All Sources</option>
                        <option value="WhatsApp" {{ ($filters['source'] ?? '') === 'WhatsApp' ? 'selected' : '' }}>WhatsApp</option>
                        <option value="Manual" {{ ($filters['source'] ?? '') === 'Manual' ? 'selected' : '' }}>Manual</option>
                        <option value="Repeat Order" {{ ($filters['source'] ?? '') === 'Repeat Order' ? 'selected' : '' }}>Repeat Order</option>
                    </select>

                    <select name="payment_status" class="text-xs bg-white border border-slate-200 rounded-lg py-2 px-3 focus:border-emerald-500">
                        <option value="">All Payment Status</option>
                        <option value="Paid" {{ ($filters['payment_status'] ?? '') === 'Paid' ? 'selected' : '' }}>Paid</option>
                        <option value="Pending" {{ ($filters['payment_status'] ?? '') === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Partially Paid" {{ ($filters['payment_status'] ?? '') === 'Partially Paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="Unpaid" {{ ($filters['payment_status'] ?? '') === 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>

                    <button type="submit" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                        Filter
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Order #</th>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-4">Source</th>
                            <th class="py-3 px-4">Items</th>
                            <th class="py-3 px-4 text-right">Amount</th>
                            <th class="py-3 px-4">Payment</th>
                            <th class="py-3 px-4">Pay Method</th>
                            <th class="py-3 px-4">Order Status</th>
                            <th class="py-3 px-4">Created</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($orders as $order)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <a href="{{ route('orders.show', $order) }}" class="text-emerald-600 hover:underline">
                                        #{{ $order->order_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <x-customer-avatar :name="$order->customer->displayName" size="xs" />
                                        <div>
                                            <p class="font-semibold text-slate-800">{{ $order->customer->displayName }}</p>
                                            <p class="text-[10px] text-slate-400">{{ $order->customer->whatsapp_number }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center gap-1 font-medium text-slate-700">
                                        @if($order->source === 'WhatsApp')
                                            <span class="w-2 h-2 rounded-full bg-[#25D366]"></span>
                                        @elseif($order->source === 'Repeat Order')
                                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                        @else
                                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                        @endif
                                        {{ $order->source }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-slate-800">{{ $order->orderItems->count() }} items</span>
                                    <span class="text-[10px] text-slate-400 block truncate max-w-[140px]">
                                        {{ $order->orderItems->pluck('product_name')->implode(', ') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <span class="font-bold text-slate-900">₹{{ number_format($order->total_amount, 2) }}</span>
                                    @if((float)$order->pending_amount > 0)
                                        <span class="block text-[10px] text-rose-500 font-semibold">₹{{ number_format($order->pending_amount, 2) }} due</span>
                                    @endif
                                </td>

                                {{-- Payment Status Badge --}}
                                <td class="py-3 px-4">
                                    @php
                                        $ps = $order->payment_status;
                                        $psBg = match($ps) {
                                            'Paid'           => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'Pending'        => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'Partially Paid' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            default          => 'bg-slate-100 text-slate-700 border-slate-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $psBg }}">
                                        {{ $ps }}
                                    </span>
                                </td>

                                {{-- Payment Method --}}
                                <td class="py-3 px-4">
                                    @php
                                        $pm = $order->payment_method;
                                        $pmIcon = match(true) {
                                            str_contains($pm, 'UPI') || str_contains($pm, 'Pay Now') => '💳',
                                            str_contains($pm, 'COD') || str_contains($pm, 'Cash on Delivery') => '🚚',
                                            str_contains($pm, 'Credit') => '🧾',
                                            str_contains($pm, 'Cash') => '💵',
                                            str_contains($pm, 'Bank') => '🏦',
                                            default => '•'
                                        };
                                    @endphp
                                    <span class="text-[11px] font-semibold text-slate-700">{{ $pmIcon }} {{ $pm }}</span>
                                </td>

                                {{-- Order Status --}}
                                <td class="py-3 px-4">
                                    <x-status-badge :status="$order->status" size="xs" />
                                </td>

                                <td class="py-3 px-4 text-[11px] text-slate-400 whitespace-nowrap">
                                    {{ $order->created_at->format('M d, H:i') }}
                                </td>
                                <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                                    <a href="{{ route('orders.show', $order) }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800">
                                        View &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-8 text-center text-xs text-slate-400">
                                    No orders found matching the filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <x-pagination :paginator="$orders" />
        </div>
    </div>
</x-layouts.app>
