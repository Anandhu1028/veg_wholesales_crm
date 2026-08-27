<x-layouts.app :headerTitle="'Order #' . $order->order_number" :headerSubtitle="'Detailed wholesale invoice, payment status and fulfillment progress'">
    <div class="p-6 max-w-[1600px] mx-auto space-y-6" x-data="{ payModalOpen: false }">

        {{-- ── 1. Top Action & Info Bar ───────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <a href="{{ route('orders.index') }}" class="p-2.5 rounded-xl border border-slate-200 text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition-colors shadow-2xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight font-mono">#{{ $order->order_number }}</h2>
                        
                        {{-- Order Status Pill --}}
                        <x-status-badge :status="$order->status" size="sm" />

                        {{-- Payment Status Pill --}}
                        @php
                            $ps = $order->payment_status;
                            $psConfig = match($ps) {
                                'Paid'           => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500'],
                                'Pending'        => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'dot' => 'bg-amber-500'],
                                'Partially Paid' => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'dot' => 'bg-blue-500'],
                                default          => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'dot' => 'bg-slate-400'],
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $psConfig['bg'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $psConfig['dot'] }}"></span>
                            {{ $ps }}
                        </span>

                        {{-- Payment Method Tag --}}
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
                        <span class="text-xs font-semibold bg-slate-100 text-slate-700 px-2.5 py-1 rounded-full border border-slate-200/80 flex items-center gap-1">
                            <span>{{ $pmIcon }}</span>
                            <span>{{ $pm }}</span>
                        </span>

                        {{-- Order Source Tag --}}
                        <span class="text-xs font-semibold bg-emerald-50 text-emerald-800 px-2.5 py-1 rounded-full border border-emerald-200/80 flex items-center gap-1">
                            @if($order->source === 'WhatsApp')
                                <span class="w-2 h-2 rounded-full bg-[#25D366]"></span>
                            @endif
                            {{ $order->source }}
                        </span>
                    </div>

                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-2">
                        <span>Placed on <strong>{{ $order->created_at->format('M d, Y \a\t h:i A') }}</strong></span>
                        @if($order->customer)
                            <span class="text-slate-300">•</span>
                            <span>Client: <strong>{{ $order->customer->displayName }}</strong></span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Action Toolbar --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Status Dropdown --}}
                <form action="{{ route('orders.update-status', $order) }}" method="POST" class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1">
                    @csrf
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Status:</label>
                    <select name="status" onchange="this.form.submit()" class="text-xs font-bold text-slate-800 bg-transparent border-0 py-1 pr-7 pl-1 focus:ring-0 cursor-pointer">
                        @foreach(['New', 'Confirmed', 'Processing', 'Ready', 'Out for Delivery', 'Delivered', 'Cancelled'] as $st)
                            <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </form>

                {{-- Record Payment button (if pending) --}}
                @if((float)$order->pending_amount > 0 || in_array($order->payment_status, ['Pending', 'Unpaid', 'Partially Paid']))
                    <button
                        type="button"
                        @click="$dispatch('open-modal', 'record-payment-modal-{{ $order->id }}')"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm hover:shadow transition-all"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Record Payment</span>
                    </button>
                @endif

                {{-- Repeat Order --}}
                <form action="{{ route('orders.repeat', $order) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200/80 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Repeat Order</span>
                    </button>
                </form>

                {{-- Open in WhatsApp --}}
                @if($order->conversation_id)
                    <a href="{{ route('inbox', ['conversation_id' => $order->conversation_id]) }}" class="px-3.5 py-2 bg-[#25D366]/10 hover:bg-[#25D366]/20 text-emerald-900 border border-emerald-300/80 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors">
                        <span class="w-2 h-2 rounded-full bg-[#25D366]"></span>
                        <span>WhatsApp Chat</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- ── 2. Main Grid: 8 cols (Left) + 4 cols (Right) ─────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- ── LEFT COLUMN (lg:col-span-8) ───────────────────────────────── --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- Produce Line Items Card --}}
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                    <div class="p-4 px-5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-base">🥬</span>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Produce Line Items</h3>
                            <span class="bg-emerald-100 text-emerald-800 text-[11px] font-bold px-2 py-0.5 rounded-full">
                                {{ $order->orderItems->count() }} {{ Str::plural('item', $order->orderItems->count()) }}
                            </span>
                        </div>
                        <span class="text-xs text-slate-400">Wholesale Rates Applied</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-50/40 text-[11px] uppercase font-bold text-slate-400 border-b border-slate-200/70">
                                <tr>
                                    <th class="py-3 px-5">Vegetable Produce</th>
                                    <th class="py-3 px-4 text-center">Quantity</th>
                                    <th class="py-3 px-4 text-right">Unit Rate</th>
                                    <th class="py-3 px-5 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($order->orderItems as $item)
                                    @php
                                        $vegEmoji = match(strtolower($item->product_name)) {
                                            'tomato' => '🍅',
                                            'onion' => '🧅',
                                            'potato' => '🥔',
                                            'carrot' => '🥕',
                                            'beans' => '🫘',
                                            'cabbage' => '🥬',
                                            'cauliflower' => '🥦',
                                            'cucumber' => '🥒',
                                            'green chilli', 'chilli' => '🌶️',
                                            'garlic' => '🧄',
                                            'ginger' => '🫚',
                                            default => '🌱',
                                        };
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3.5 px-5">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-sm shrink-0">
                                                    {{ $vegEmoji }}
                                                </div>
                                                <div>
                                                    <span class="font-bold text-slate-900 text-sm block">{{ $item->product_name }}</span>
                                                    <span class="text-[10px] text-slate-400">Fresh Wholesale Harvest</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <span class="inline-block bg-slate-100 text-slate-800 font-bold px-2.5 py-1 rounded-lg font-mono text-xs">
                                                {{ (float)$item->quantity }} {{ $item->unit }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-mono text-slate-700">
                                            ₹{{ number_format($item->unit_price, 2) }} <span class="text-[10px] text-slate-400">/ {{ $item->unit }}</span>
                                        </td>
                                        <td class="py-3.5 px-5 text-right font-bold text-slate-900 font-mono text-sm">
                                            ₹{{ number_format($item->subtotal, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Invoice Calculation Footer --}}
                    <div class="p-5 bg-slate-50/70 border-t border-slate-200/80">
                        <div class="max-w-xs ml-auto space-y-2 text-xs">
                            <div class="flex justify-between text-slate-600">
                                <span>Produce Subtotal:</span>
                                <span class="font-bold text-slate-800 font-mono">₹{{ number_format($order->subtotal, 2) }}</span>
                            </div>
                            @if((float)$order->discount > 0)
                                <div class="flex justify-between text-emerald-600">
                                    <span>Discount:</span>
                                    <span class="font-bold font-mono">- ₹{{ number_format($order->discount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-slate-600">
                                <span>Delivery Fee:</span>
                                <span class="font-bold text-emerald-700">{{ (float)$order->delivery_charge > 0 ? '₹' . number_format($order->delivery_charge, 2) : 'FREE' }}</span>
                            </div>
                            <div class="flex justify-between items-baseline text-sm font-extrabold text-slate-900 border-t border-slate-200 pt-2.5 mt-1">
                                <span class="text-xs uppercase tracking-wider text-slate-700">Total Invoice:</span>
                                <span class="text-xl font-mono text-emerald-700">₹{{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payment Overview & Ledger Breakdown Card --}}
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-base">💳</span>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Payment Breakdown</h3>
                        </div>
                        @if((float)$order->pending_amount > 0 || in_array($order->payment_status, ['Pending', 'Unpaid', 'Partially Paid']))
                            <button
                                type="button"
                                @click="$dispatch('open-modal', 'record-payment-modal-{{ $order->id }}')"
                                class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-lg text-xs font-bold flex items-center gap-1 transition-colors"
                            >
                                <span>+ Record Payment</span>
                            </button>
                        @endif
                    </div>

                    {{-- 4-Pill Metric Grid --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Payment Method</span>
                            <span class="text-sm font-extrabold text-slate-900 flex items-center gap-1.5">
                                <span>{{ $pmIcon }}</span>
                                <span>{{ $order->payment_method }}</span>
                            </span>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block mb-1">Payment Status</span>
                            <span class="inline-flex items-center gap-1 text-xs font-bold {{ $psConfig['bg'] }} px-2 py-0.5 rounded-md border">
                                <span class="w-1.5 h-1.5 rounded-full {{ $psConfig['dot'] }}"></span>
                                {{ $order->payment_status }}
                            </span>
                        </div>

                        <div class="p-3.5 rounded-xl bg-emerald-50/50 border border-emerald-100">
                            <span class="text-[10px] font-bold uppercase text-emerald-800 block mb-1">Paid Amount</span>
                            <span class="text-base font-extrabold text-emerald-700 font-mono">
                                ₹{{ number_format($order->paid_amount ?? 0, 2) }}
                            </span>
                        </div>

                        <div class="p-3.5 rounded-xl {{ (float)($order->pending_amount ?? $order->total_amount) > 0 ? 'bg-rose-50/50 border border-rose-100' : 'bg-slate-50 border border-slate-200/70' }}">
                            <span class="text-[10px] font-bold uppercase {{ (float)($order->pending_amount ?? $order->total_amount) > 0 ? 'text-rose-700' : 'text-slate-400' }} block mb-1">Pending Amount</span>
                            <span class="text-base font-extrabold {{ (float)($order->pending_amount ?? $order->total_amount) > 0 ? 'text-rose-600' : 'text-slate-400' }} font-mono">
                                ₹{{ number_format($order->pending_amount ?? 0, 2) }}
                            </span>
                        </div>
                    </div>

                    {{-- Partial Payment Alert Notice (if applicable) --}}
                    @if($order->payment_status === 'Partially Paid')
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-900 font-semibold flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-base">ℹ️</span>
                                <span>Partial payment received (₹{{ number_format($order->paid_amount, 2) }} of ₹{{ number_format($order->total_amount, 2) }}). Balance of ₹{{ number_format($order->pending_amount, 2) }} is pending collection.</span>
                            </div>
                            <button
                                type="button"
                                @click="$dispatch('open-modal', 'record-payment-modal-{{ $order->id }}')"
                                class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-[11px] font-bold shrink-0 ml-2"
                            >
                                Settle Balance
                            </button>
                        </div>
                    @endif

                    {{-- Transaction History Table --}}
                    @if($order->payments->isNotEmpty())
                        <div class="pt-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-2">Payment Vouchers ({{ $order->payments->count() }})</span>
                            <div class="overflow-x-auto rounded-xl border border-slate-200/80">
                                <table class="w-full text-xs text-slate-600">
                                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                                        <tr>
                                            <th class="py-2.5 px-3.5">Receipt #</th>
                                            <th class="py-2.5 px-3">Date</th>
                                            <th class="py-2.5 px-3 text-right">Amount</th>
                                            <th class="py-2.5 px-3">Method</th>
                                            <th class="py-2.5 px-3">Reference</th>
                                            <th class="py-2.5 px-3">Logged By</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($order->payments as $pay)
                                            <tr class="hover:bg-slate-50">
                                                <td class="py-2.5 px-3.5 font-mono font-bold text-slate-900">{{ $pay->payment_number }}</td>
                                                <td class="py-2.5 px-3 text-slate-600">{{ $pay->payment_date->format('M d, Y') }}</td>
                                                <td class="py-2.5 px-3 text-right font-bold text-emerald-700 font-mono">₹{{ number_format($pay->amount, 2) }}</td>
                                                <td class="py-2.5 px-3 font-semibold text-slate-800">{{ $pay->payment_method }}</td>
                                                <td class="py-2.5 px-3 font-mono text-[11px] text-slate-400">{{ $pay->reference_number ?: $pay->reference ?: 'N/A' }}</td>
                                                <td class="py-2.5 px-3 text-slate-500">{{ $pay->receiver?->name ?? 'System Bot' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

            </div>

            {{-- ── RIGHT COLUMN (lg:col-span-4) ──────────────────────────────── --}}
            <div class="lg:col-span-4 space-y-6">

                {{-- Order Lifecycle Stepper --}}
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-base">⏱️</span>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">Fulfillment Lifecycle</h4>
                        </div>
                        <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">
                            {{ $order->status }}
                        </span>
                    </div>

                    @php
                        $stages = [
                            'New'              => ['desc' => 'Order received in system', 'icon' => '📥'],
                            'Confirmed'        => ['desc' => 'Matched & payment verified', 'icon' => '✅'],
                            'Processing'       => ['desc' => 'Cold room packing in crates', 'icon' => '📦'],
                            'Ready'            => ['desc' => 'Staged at loading dock', 'icon' => '🏷️'],
                            'Out for Delivery' => ['desc' => 'Dispatched on morning route', 'icon' => '🚚'],
                            'Delivered'        => ['desc' => 'Signed & delivered to client', 'icon' => '🎉'],
                        ];
                        $stageKeys = array_keys($stages);
                        $currentIndex = array_search($order->status, $stageKeys);
                        if ($currentIndex === false) $currentIndex = ($order->status === 'Cancelled' ? -1 : 1);
                    @endphp

                    <div class="space-y-3 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-slate-200 before:z-0">
                        @foreach($stages as $stageName => $meta)
                            @php
                                $sIdx = array_search($stageName, $stageKeys);
                                $isDone = $sIdx < $currentIndex;
                                $isCurrent = $sIdx === $currentIndex;
                                $isPending = $sIdx > $currentIndex;
                            @endphp
                            <div class="relative z-10 flex items-start gap-3">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0 transition-all {{ $isDone ? 'bg-emerald-600 text-white shadow-xs' : ($isCurrent ? 'bg-emerald-600 text-white ring-4 ring-emerald-100 shadow-xs' : 'bg-white border-2 border-slate-200 text-slate-400') }}">
                                    @if($isDone)
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        <span>{{ $sIdx + 1 }}</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0 pt-0.5">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs font-bold {{ $isDone || $isCurrent ? 'text-slate-900' : 'text-slate-400' }}">
                                            {{ $stageName }}
                                        </p>
                                        @if($isCurrent)
                                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded">Current</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] {{ $isDone || $isCurrent ? 'text-slate-500' : 'text-slate-400' }}">
                                        {{ $meta['desc'] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Customer Profile Card --}}
                @if($order->customer)
                    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-base">👤</span>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">Customer Details</h4>
                            </div>
                            <a href="{{ route('customers.show', $order->customer) }}" class="text-[11px] font-bold text-emerald-600 hover:underline">
                                Profile &rarr;
                            </a>
                        </div>

                        <div class="flex items-center gap-3">
                            <x-customer-avatar :name="$order->customer->displayName" size="md" />
                            <div class="min-w-0 flex-1">
                                <h5 class="text-sm font-bold text-slate-900 truncate">{{ $order->customer->displayName }}</h5>
                                <p class="text-xs text-slate-500">{{ $order->customer->name }}</p>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->customer->whatsapp_number) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 hover:underline mt-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#25D366]"></span>
                                    <span>{{ $order->customer->whatsapp_number }}</span>
                                </a>
                            </div>
                        </div>

                        {{-- Financial Ledger Status for this client --}}
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 space-y-2 text-xs">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Business Type:</span>
                                <span class="font-bold text-slate-800">{{ $order->customer->business_type ?: 'Wholesale Buyer' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Total Outstanding:</span>
                                <span class="font-extrabold {{ (float)$order->customer->outstanding_balance > 0 ? 'text-rose-600' : 'text-slate-700' }} font-mono">
                                    ₹{{ number_format($order->customer->outstanding_balance, 2) }}
                                </span>
                            </div>
                            @if($order->customer->credit_enabled)
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Credit Limit:</span>
                                    <span class="font-bold text-slate-700 font-mono">₹{{ number_format($order->customer->credit_limit, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Available Credit:</span>
                                    <span class="font-extrabold text-emerald-700 font-mono">₹{{ number_format($order->customer->available_credit, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Delivery & Fleet Dispatch Card --}}
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-base">🚚</span>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">Delivery & Fleet</h4>
                        </div>
                        <span class="text-[11px] font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-full">
                            {{ $order->delivery?->status ?? 'Scheduled' }}
                        </span>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-start gap-2.5">
                            <span class="text-slate-400 mt-0.5">📅</span>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Delivery Window</span>
                                <span class="font-bold text-slate-900 text-xs">
                                    {{ $order->delivery?->delivery_date ? $order->delivery->delivery_date->format('l, M d, Y') : ($order->delivery_date ? $order->delivery_date->format('l, M d, Y') : 'Tomorrow Morning') }}
                                </span>
                                <span class="text-[11px] text-slate-500 block font-medium">{{ $order->delivery?->time_slot ?: ($order->time_slot ?: 'Morning (6:00 AM - 9:00 AM)') }}</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5">
                            <span class="text-slate-400 mt-0.5">📍</span>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Drop-off Address</span>
                                <p class="font-semibold text-slate-800 leading-snug">
                                    {{ $order->delivery_address ?: ($order->customer?->address ?: 'Dubai Central Wholesale Produce Market, Al Aweer') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5 pt-2 border-t border-slate-100">
                            <span class="text-slate-400 mt-0.5">🚐</span>
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Assigned Driver & Van</span>
                                <span class="font-bold text-slate-900 block">{{ $order->delivery?->driver_name ?: 'Rashid Khan' }}</span>
                                <span class="text-[11px] font-mono text-slate-500">{{ $order->delivery?->vehicle_number ?: 'DXB-VAN-4028' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- ── 3. Record Payment Modal ────────────────────────────────────────── --}}
    <x-modal name="record-payment-modal-{{ $order->id }}" title="Record Customer Payment — #{{ $order->order_number }}" maxWidth="md">
        <form action="{{ route('orders.record-payment', $order) }}" method="POST" class="space-y-4">
            @csrf

            <div class="p-4 bg-emerald-50/50 rounded-xl border border-emerald-200/80 text-xs space-y-2">
                <div class="flex justify-between">
                    <span class="text-slate-500">Order Invoice:</span>
                    <span class="font-bold text-slate-900 font-mono">#{{ $order->order_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Client Name:</span>
                    <span class="font-bold text-slate-800">{{ $order->customer->displayName }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Total Order Value:</span>
                    <span class="font-extrabold text-slate-900 font-mono">₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Already Collected:</span>
                    <span class="font-bold text-emerald-700 font-mono">₹{{ number_format($order->paid_amount ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between border-t border-emerald-200/60 pt-2 text-sm font-bold">
                    <span class="text-rose-700">Outstanding Due:</span>
                    <span class="text-rose-700 font-mono text-base">₹{{ number_format($order->pending_amount ?? $order->total_amount, 2) }}</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Payment Amount to Collect (₹) <span class="text-rose-500">*</span></label>
                <input
                    type="number"
                    step="0.01"
                    name="amount"
                    value="{{ $order->pending_amount ?? $order->total_amount }}"
                    required
                    class="w-full text-base font-bold rounded-xl border-slate-300 py-2.5 font-mono text-slate-900 focus:border-emerald-500 focus:ring-emerald-500"
                >
                <p class="text-[10px] text-slate-400 mt-1">Supports partial settlements. Outstanding balance will update automatically.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Collection Channel</label>
                <select name="payment_method" class="w-full text-xs font-semibold rounded-xl border-slate-300 py-2.5 text-slate-800 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="Cash">💵 Cash on Delivery / Handover</option>
                    <option value="UPI">📱 UPI / Instant Settlement</option>
                    <option value="Bank Transfer">🏦 Bank Transfer (WPS / IBAN)</option>
                    <option value="Card">💳 Card POS Terminal</option>
                    <option value="Cheque">📄 Cheque Clearing</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Transaction Ref / Cheque #</label>
                <input type="text" name="reference_number" placeholder="e.g. TXN-948201 or Cheque 004812" class="w-full text-xs rounded-xl border-slate-300 py-2 font-mono">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Payment Date</label>
                <input type="date" name="payment_date" value="{{ now()->toDateString() }}" class="w-full text-xs rounded-xl border-slate-300 py-2">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Staff Memo / Notes</label>
                <textarea name="notes" rows="2" placeholder="e.g. Collected by driver Rashid upon morning delivery" class="w-full text-xs rounded-xl border-slate-300 p-2.5"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" @click="$dispatch('close-modal', 'record-payment-modal-{{ $order->id }}')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs">
                    Confirm & Update Ledger
                </button>
            </div>
        </form>
    </x-modal>
</x-layouts.app>
