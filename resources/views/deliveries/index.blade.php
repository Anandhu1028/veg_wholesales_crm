<x-layouts.app :headerTitle="'Dispatch & Deliveries'" :headerSubtitle="'Fleet operations, morning run-sheets and driver tracking'">
    <div class="p-6 space-y-6">
        <!-- Status Filter Tabs -->
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-xs flex items-center gap-2 overflow-x-auto text-xs font-semibold">
            <a href="{{ route('deliveries.index') }}" class="px-3 py-1.5 rounded-lg {{ !request('status') ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                All ({{ array_sum($statusCounts) }})
            </a>
            <a href="{{ route('deliveries.index', ['status' => 'Pending']) }}" class="px-3 py-1.5 rounded-lg {{ request('status') === 'Pending' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Pending ({{ $statusCounts['Pending'] }})
            </a>
            <a href="{{ route('deliveries.index', ['status' => 'Preparing']) }}" class="px-3 py-1.5 rounded-lg {{ request('status') === 'Preparing' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Preparing ({{ $statusCounts['Preparing'] }})
            </a>
            <a href="{{ route('deliveries.index', ['status' => 'Ready']) }}" class="px-3 py-1.5 rounded-lg {{ request('status') === 'Ready' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Ready ({{ $statusCounts['Ready'] }})
            </a>
            <a href="{{ route('deliveries.index', ['status' => 'Out for Delivery']) }}" class="px-3 py-1.5 rounded-lg {{ request('status') === 'Out for Delivery' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Out for Delivery ({{ $statusCounts['Out for Delivery'] }})
            </a>
            <a href="{{ route('deliveries.index', ['status' => 'Delivered']) }}" class="px-3 py-1.5 rounded-lg {{ request('status') === 'Delivered' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                Delivered ({{ $statusCounts['Delivered'] }})
            </a>
        </div>

        <!-- Deliveries Table -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Order #</th>
                            <th class="py-3 px-4">Customer</th>
                            <th class="py-3 px-4">Delivery Location</th>
                            <th class="py-3 px-4">Schedule</th>
                            <th class="py-3 px-4">Assigned Driver & Van</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Update Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($deliveries as $del)
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <a href="{{ route('orders.show', $del->order) }}" class="text-emerald-600 hover:underline">
                                        #{{ $del->order->order_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <p class="font-bold text-slate-800">{{ $del->order->customer->displayName }}</p>
                                    <p class="text-[10px] text-slate-400 font-mono">{{ $del->order->customer->whatsapp_number }}</p>
                                </td>
                                <td class="py-3 px-4 max-w-xs text-slate-700">
                                    {{ $del->order->delivery_address }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-slate-800 block">{{ $del->delivery_date ? $del->delivery_date->format('M d, Y') : 'Tomorrow' }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $del->time_slot }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-medium text-slate-800 block">{{ $del->driver_name ?: 'Rashid Khan' }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $del->vehicle_number ?: 'DXB-VAN-4028' }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <x-status-badge :status="$del->status" size="xs" />
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <form action="{{ route('deliveries.update-status', $del) }}" method="POST" class="inline-block">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()" class="text-xs font-semibold rounded-lg border-slate-300 py-1 px-2">
                                            @foreach(['Pending', 'Preparing', 'Ready', 'Out for Delivery', 'Delivered', 'Failed'] as $st)
                                                <option value="{{ $st }}" {{ $del->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-xs text-slate-400">No deliveries matching criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$deliveries" />
        </div>
    </div>
</x-layouts.app>
