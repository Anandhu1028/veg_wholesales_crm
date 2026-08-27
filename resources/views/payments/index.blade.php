<x-layouts.app :headerTitle="'Payments & Customer Ledger'" :headerSubtitle="'Track receivables, cash collections, bank transfers and credit accounts'">
    <div class="p-6 space-y-6" x-data="{ recordModalOpen: false }">
        <!-- Top Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-stat-card
                title="Total Collections"
                :value="'₹' . number_format($totalCollected, 0)"
                subtitle="Completed payment vouchers"
                color="emerald"
            />
            <x-stat-card
                title="Total Market Outstanding"
                :value="'₹' . number_format($totalOutstanding, 0)"
                subtitle="Receivable from wholesale clients"
                color="rose"
            />
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Payment Transactions</h3>
                    <p class="text-xs text-slate-500">Receipts allocated against orders and general customer ledger</p>
                </div>

                <button
                    type="button"
                    @click="$dispatch('open-modal', 'record-payment-modal')"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>+ Record Payment</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Receipt #</th>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-4">Order Ref</th>
                            <th class="py-3 px-4 text-right">Amount Paid</th>
                            <th class="py-3 px-4">Payment Method</th>
                            <th class="py-3 px-4">Reference / Txn ID</th>
                            <th class="py-3 px-4">Payment Date</th>
                            <th class="py-3 px-4">Received By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($payments as $pay)
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-3 px-4 font-bold text-slate-900 font-mono">
                                    {{ $pay->payment_number }}
                                </td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('customers.show', $pay->customer) }}" class="font-bold text-slate-800 hover:text-emerald-600">
                                        {{ $pay->customer->displayName }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    @if($pay->order)
                                        <a href="{{ route('orders.show', $pay->order) }}" class="text-emerald-600 font-bold hover:underline">
                                            #{{ $pay->order->order_number }}
                                        </a>
                                    @else
                                        <span class="text-slate-400">Account Advance</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-700 text-sm">
                                    ₹{{ number_format($pay->amount, 2) }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded font-semibold text-[11px]">
                                        {{ $pay->payment_method }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-500">
                                    {{ $pay->reference_number ?: 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $pay->payment_date->format('M d, Y') }}
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $pay->receiver?->name ?? 'System/Accounts' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-xs text-slate-400">No payment records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$payments" />
        </div>
    </div>

    <!-- Record Payment Modal -->
    <x-modal name="record-payment-modal" title="Record Customer Payment" maxWidth="md">
        <form action="{{ route('payments.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Customer <span class="text-rose-500">*</span></label>
                <select name="customer_id" required class="w-full text-xs rounded-lg border-slate-300 py-2">
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->displayName }} (Bal: ₹{{ number_format($c->outstanding_balance, 0) }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Allocate to Specific Order (Optional)</label>
                <select name="order_id" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    <option value="">General Account Credit</option>
                    @foreach($unpaidOrders as $uo)
                        <option value="{{ $uo->id }}">#{{ $uo->order_number }} - {{ $uo->customer->displayName }} (Due: ₹{{ number_format($uo->due_amount, 2) }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Amount Paid (₹) <span class="text-rose-500">*</span></label>
                <input type="number" step="0.5" name="amount" required placeholder="e.g. 5000" class="w-full text-xs rounded-lg border-slate-300 py-2 font-mono">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Method</label>
                <select name="payment_method" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    <option value="Cash">Cash on Delivery</option>
                    <option value="Bank Transfer">Bank Transfer (WPS / IBAN)</option>
                    <option value="UPI">UPI / Instant</option>
                    <option value="Cheque">Cheque</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Transaction Reference / Cheque #</label>
                <input type="text" name="reference_number" placeholder="e.g. TXN-8921820" class="w-full text-xs rounded-lg border-slate-300 py-2 font-mono">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Date</label>
                <input type="date" name="payment_date" value="{{ now()->toDateString() }}" class="w-full text-xs rounded-lg border-slate-300 py-2">
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" @click="$dispatch('close-modal', 'record-payment-modal')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs">
                    Save Payment & Settle Ledger
                </button>
            </div>
        </form>
    </x-modal>
</x-layouts.app>
