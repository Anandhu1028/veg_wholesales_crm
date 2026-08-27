<x-layouts.app :headerTitle="$customer->displayName" :headerSubtitle="'Customer profile, order history, payments and custom pricing'">
    <div class="p-6 space-y-6" x-data="{ activeTab: 'orders' }">
        <!-- Top Profile Header -->
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('customers.index') }}" class="p-2 rounded-lg border border-slate-200 text-slate-500 hover:text-slate-900 hover:bg-slate-50">
                    &larr;
                </a>
                <x-customer-avatar :name="$customer->displayName" size="lg" />
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-slate-900">{{ $customer->displayName }}</h2>
                        <x-status-badge :status="$customer->status" size="xs" />
                        <span class="text-xs font-semibold bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full border border-emerald-200">
                            {{ $customer->business_type }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Contact Person: {{ $customer->name }} • WhatsApp: <span class="font-mono text-slate-700">{{ $customer->whatsapp_number }}</span></p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('customers.edit', $customer) }}" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                    Edit Profile
                </a>
                @if($customer->conversations->isNotEmpty())
                    <a href="{{ route('inbox', ['conversation_id' => $customer->conversations->first()->id]) }}" class="px-3.5 py-1.5 bg-[#25D366] hover:bg-[#1faa52] text-white rounded-lg text-xs font-semibold flex items-center gap-1.5 shadow-xs transition-colors">
                        <span>💬 Open WhatsApp Chat</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- 4 KPI Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card title="Total Orders" :value="$stats['total_orders']" color="blue" />
            <x-stat-card title="Total Spent" :value="'₹' . number_format($stats['total_spent'], 2)" color="emerald" />
            <x-stat-card title="Outstanding" :value="'₹' . number_format($stats['outstanding_balance'], 2)" color="rose" />
            <x-stat-card title="Avg Order Value" :value="'₹' . number_format($stats['avg_order_value'], 2)" color="purple" />
        </div>

        <!-- Tabs Navigation -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="border-b border-slate-200 px-5 flex items-center gap-6 text-xs font-semibold bg-slate-50/50">
                <button
                    @click="activeTab = 'orders'"
                    :class="activeTab === 'orders' ? 'border-emerald-600 text-emerald-700 border-b-2' : 'text-slate-500 hover:text-slate-900'"
                    class="py-3 transition-colors"
                >
                    Order History ({{ $customer->orders->count() }})
                </button>
                <button
                    @click="activeTab = 'pricing'"
                    :class="activeTab === 'pricing' ? 'border-emerald-600 text-emerald-700 border-b-2' : 'text-slate-500 hover:text-slate-900'"
                    class="py-3 transition-colors"
                >
                    Customer-Specific Pricing ({{ $customer->customPrices->count() }})
                </button>
                <button
                    @click="activeTab = 'payments'"
                    :class="activeTab === 'payments' ? 'border-emerald-600 text-emerald-700 border-b-2' : 'text-slate-500 hover:text-slate-900'"
                    class="py-3 transition-colors"
                >
                    Payments & Ledger ({{ $customer->payments->count() }})
                </button>
                <button
                    @click="activeTab = 'info'"
                    :class="activeTab === 'info' ? 'border-emerald-600 text-emerald-700 border-b-2' : 'text-slate-500 hover:text-slate-900'"
                    class="py-3 transition-colors"
                >
                    Addresses & Notes
                </button>
            </div>

            <!-- Tab 1: Orders -->
            <div x-show="activeTab === 'orders'" class="p-4">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Order #</th>
                                <th class="py-3 px-4">Source</th>
                                <th class="py-3 px-4">Items</th>
                                <th class="py-3 px-4 text-right">Amount</th>
                                <th class="py-3 px-4">Payment</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Date</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($customer->orders as $order)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-3 px-4 font-bold text-slate-900">
                                        <a href="{{ route('orders.show', $order) }}" class="text-emerald-600 hover:underline">
                                            #{{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-4">{{ $order->source }}</td>
                                    <td class="py-3 px-4">{{ $order->orderItems->count() }} items</td>
                                    <td class="py-3 px-4 text-right font-bold text-slate-900">₹{{ number_format($order->total_amount, 2) }}</td>
                                    <td class="py-3 px-4"><x-status-badge :status="$order->payment_status" size="xs" /></td>
                                    <td class="py-3 px-4"><x-status-badge :status="$order->status" size="xs" /></td>
                                    <td class="py-3 px-4 text-[11px] text-slate-400">{{ $order->created_at->format('M d, Y') }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('orders.show', $order) }}" class="text-xs font-semibold text-emerald-600 hover:underline">
                                            Details &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-6 text-center text-xs text-slate-400">No orders for this customer yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: Customer Specific Pricing -->
            <div x-show="activeTab === 'pricing'" x-cloak class="p-6 space-y-6">
                <div class="flex items-center justify-between bg-emerald-50/60 p-4 rounded-xl border border-emerald-200">
                    <div>
                        <h4 class="text-xs font-bold text-emerald-900 uppercase tracking-wider">Custom Wholesale Rate Card</h4>
                        <p class="text-xs text-emerald-700 mt-0.5">When this customer places an order, the system prioritizes these custom rates over default product rates.</p>
                    </div>
                </div>

                <!-- Add/Update Custom Price Form -->
                <form action="{{ route('customers.custom-price', $customer) }}" method="POST" class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 flex flex-wrap items-end gap-3">
                    @csrf
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Select Product</label>
                        <select name="product_id" required class="w-full text-xs rounded-lg border-slate-300 py-2">
                            @foreach($allProducts as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} (Default: ₹{{ $p->base_price }}/{{ $p->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-36">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Custom Rate (₹)</label>
                        <input type="number" step="0.5" name="custom_price" required placeholder="e.g. 37.00" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs">
                        Save Rate
                    </button>
                </form>

                <!-- Custom Prices Table -->
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Vegetable</th>
                            <th class="py-3 px-4">Unit</th>
                            <th class="py-3 px-4 text-right">Default Catalog Rate</th>
                            <th class="py-3 px-4 text-right">Customer Specific Rate</th>
                            <th class="py-3 px-4 text-right">Discount Given</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($customer->customPrices as $cp)
                            @php
                                $diff = (float)$cp->product->base_price - (float)$cp->custom_price;
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-4 font-bold text-slate-900">{{ $cp->product->name }}</td>
                                <td class="py-3 px-4">{{ $cp->product->unit }}</td>
                                <td class="py-3 px-4 text-right text-slate-400">₹{{ number_format($cp->product->base_price, 2) }}</td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-700">₹{{ number_format($cp->custom_price, 2) }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-slate-700">
                                    {{ $diff > 0 ? '₹' . number_format($diff, 2) . ' OFF' : 'Standard' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-xs text-slate-400">No custom pricing set. Default catalog rates apply.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Tab 3: Payments & Ledger -->
            <div x-show="activeTab === 'payments'" x-cloak class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Payment Transactions</h4>
                    <span class="text-xs font-bold text-slate-700">Current Outstanding: <strong class="text-rose-600">₹{{ number_format($customer->outstanding_balance, 2) }}</strong></span>
                </div>

                <div class="space-y-2">
                    @forelse($customer->payments as $pay)
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-slate-900">{{ $pay->payment_number }}</span>
                                <span class="text-slate-500 ml-2">via {{ $pay->payment_method }} (Ref: {{ $pay->reference_number ?: 'N/A' }})</span>
                                <p class="text-[10px] text-slate-400 mt-0.5">Received on {{ $pay->payment_date->format('M d, Y') }}</p>
                            </div>
                            <span class="font-bold text-emerald-700 text-sm">₹{{ number_format($pay->amount, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-6 text-center">No payment entries in ledger.</p>
                    @endforelse
                </div>
            </div>

            <!-- Tab 4: Info & Addresses -->
            <div x-show="activeTab === 'info'" x-cloak class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                        <h4 class="font-bold text-slate-900">Address & Delivery Location</h4>
                        <p class="text-slate-700">{{ $customer->address ?: 'No physical address stored' }}</p>
                        <p class="text-slate-500">City: {{ $customer->city }}</p>
                        <p class="text-slate-500">Credit Limit: ₹{{ number_format($customer->credit_limit, 2) }}</p>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                        <h4 class="font-bold text-slate-900">Account Notes</h4>
                        <p class="text-slate-700 whitespace-pre-line">{{ $customer->notes ?: 'No special notes.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
