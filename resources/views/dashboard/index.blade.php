<x-layouts.app :headerTitle="'Dashboard'" :headerSubtitle="'Real-time overview of orders, WhatsApp conversations and operations'">
    <div class="p-6 space-y-6">
        <!-- Top Stats Row — Orders + Payments -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <x-stat-card
                title="Connected Numbers"
                :value="$connectedNumbers"
                subtitle="🟢 All lines active"
                color="emerald"
                :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z\'/></svg>'"
            />

            <x-stat-card
                title="Conversations"
                :value="$totalConversations"
                subtitle="WhatsApp chat sessions"
                color="blue"
                :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z\'/></svg>'"
            />

            <x-stat-card
                title="New Orders"
                :value="$newOrders"
                subtitle="Awaiting confirmation"
                color="amber"
                :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z\'/></svg>'"
            />

            <x-stat-card
                title="Pending Orders"
                :value="$pendingOrders"
                subtitle="In packing / dispatch"
                color="purple"
                :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10\'/></svg>'"
            />

            <x-stat-card
                title="Delivered Today"
                :value="$deliveredToday"
                subtitle="Morning dispatch fulfilled"
                color="emerald"
                :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\'/></svg>'"
            />

            <x-stat-card
                title="Pending Balance"
                :value="'₹' . number_format($pendingPayments, 0)"
                subtitle="Total customer ledger"
                color="rose"
                :icon="'<svg class=\'w-5 h-5\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z\'/></svg>'"
            />
        </div>

        <!-- Payment Stats Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs">
                <p class="text-[11px] font-bold uppercase text-slate-400 mb-1">Today's Sales</p>
                <p class="text-2xl font-extrabold text-slate-900">₹{{ number_format($todaysSales, 0) }}</p>
                <p class="text-xs text-slate-500 mt-1">All confirmed orders today</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs">
                <p class="text-[11px] font-bold uppercase text-slate-400 mb-1">Paid Today</p>
                <p class="text-2xl font-extrabold text-emerald-600">₹{{ number_format($paidToday, 0) }}</p>
                <p class="text-xs text-slate-500 mt-1">Cash + UPI + Bank collected</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs">
                <p class="text-[11px] font-bold uppercase text-slate-400 mb-1">Pending Payments</p>
                <p class="text-2xl font-extrabold text-rose-600">₹{{ number_format($pendingToday, 0) }}</p>
                <p class="text-xs text-slate-500 mt-1">COD + Credit not collected</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs flex items-center gap-4">
                <div class="flex-1">
                    <p class="text-[11px] font-bold uppercase text-slate-400 mb-2">Today's Order Types</p>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-lg">🚚</span>
                        <span class="text-sm font-bold text-amber-600">{{ $codOrdersToday }}</span>
                        <span class="text-xs text-slate-500">COD Orders</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🧾</span>
                        <span class="text-sm font-bold text-blue-600">{{ $creditOrdersToday }}</span>
                        <span class="text-xs text-slate-500">Credit Orders</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Middle Section: Sales Overview Chart & WhatsApp Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Weekly Sales Overview -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Weekly Sales Overview</h3>
                        <p class="text-xs text-slate-500">Gross revenue generated from WhatsApp and direct orders</p>
                    </div>
                    <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                        Past 7 Days
                    </span>
                </div>

                <!-- Clean Weekly Bar Chart Visualization -->
                <div class="pt-4 pb-2">
                    <div class="flex items-end justify-between gap-3 h-44 px-2">
                        @php
                            $maxAmount = max($salesAmounts) > 0 ? max($salesAmounts) : 1;
                        @endphp
                        @foreach($salesDays as $idx => $day)
                            @php
                                $amt = $salesAmounts[$idx] ?? 0;
                                $heightPct = max(12, min(100, round(($amt / $maxAmount) * 100)));
                            @endphp
                            <div class="flex-1 flex flex-col items-center gap-2 group">
                                <div class="text-[11px] font-semibold text-slate-700 opacity-0 group-hover:opacity-100 transition-opacity">
                                    ₹{{ number_format($amt) }}
                                </div>
                                <div class="w-full bg-slate-100 rounded-t-lg h-36 flex items-end p-1">
                                    <div
                                        class="w-full bg-emerald-500 hover:bg-emerald-600 rounded-t transition-all duration-300 group-hover:shadow-md"
                                        style="height: {{ $heightPct }}%;"
                                    ></div>
                                </div>
                                <span class="text-xs font-medium text-slate-500">{{ $day }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- WhatsApp Activity Stream -->
            <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-xs flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-[#25D366] animate-ping"></div>
                        <h3 class="text-sm font-bold text-slate-900">WhatsApp Activity</h3>
                    </div>
                    <a href="{{ route('inbox') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Open Inbox &rarr;</a>
                </div>

                <div class="space-y-3 flex-1 overflow-y-auto">
                    @forelse($recentActivities as $msg)
                        <a href="{{ route('inbox', ['conversation_id' => $msg->conversation_id]) }}" class="block p-2.5 rounded-lg border border-slate-100 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold text-slate-800 truncate">
                                    {{ $msg->conversation?->customer?->displayName ?? 'Customer' }}
                                </span>
                                <span class="text-[10px] text-slate-400">
                                    {{ $msg->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 line-clamp-1">
                                <span class="font-semibold text-slate-500">
                                    {{ $msg->sender_type === 'customer' ? 'Customer:' : ($msg->sender_type === 'bot' ? '🤖 Bot:' : 'Staff:') }}
                                </span>
                                {{ $msg->body }}
                            </p>
                        </a>
                    @empty
                        <p class="text-xs text-slate-400 py-6 text-center">No recent WhatsApp activity yet.</p>
                    @endforelse
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        x-data
                        @click="$dispatch('open-modal', 'simulate-whatsapp-modal')"
                        class="w-full py-2 text-xs font-semibold text-[#25D366] bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors flex items-center justify-center gap-1.5"
                    >
                        <span>+ Simulate Incoming Message</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Recent Wholesale Orders</h3>
                    <p class="text-xs text-slate-500">Latest orders submitted via WhatsApp and operations desk</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('orders.create') }}" class="px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition-colors">
                        + New Order
                    </a>
                    <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50">
                        View All
                    </a>
                </div>
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
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Created</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentOrders as $order)
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
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-700">
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
                                    <span class="text-xs text-slate-700 font-medium">{{ $order->orderItems->count() }} items</span>
                                    <span class="text-[10px] text-slate-400 block truncate max-w-xs">
                                        {{ $order->orderItems->pluck('product_name')->implode(', ') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900">
                                    ₹{{ number_format($order->total_amount, 2) }}
                                </td>
                                <td class="py-3 px-4">
                                    <x-status-badge :status="$order->payment_status" size="xs" />
                                </td>
                                <td class="py-3 px-4">
                                    <x-status-badge :status="$order->status" size="xs" />
                                </td>
                                <td class="py-3 px-4 text-[11px] text-slate-400 whitespace-nowrap">
                                    {{ $order->created_at->format('M d, H:i') }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('orders.show', $order) }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800">
                                        Details &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-6 text-center text-xs text-slate-400">No recent orders.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
