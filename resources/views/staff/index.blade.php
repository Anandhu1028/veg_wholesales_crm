<x-layouts.app :headerTitle="'Staff & Team Access'" :headerSubtitle="'Manage team members, roles and dashboard permissions'">
    <div class="p-6 space-y-6">
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Operations Team</h3>
                    <p class="text-xs text-slate-500">Access control for Admin, Order Managers, Accounts and Drivers</p>
                </div>

                <button
                    type="button"
                    @click="$dispatch('open-modal', 'add-staff-modal')"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs flex items-center gap-1.5 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Add Team Member</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase font-semibold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Staff Name</th>
                            <th class="py-3 px-4">Email</th>
                            <th class="py-3 px-4">Assigned Role</th>
                            <th class="py-3 px-4">Phone</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Created</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($staffMembers as $member)
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-slate-800 text-white font-bold text-xs flex items-center justify-center">
                                            {{ substr($member->name, 0, 1) }}
                                        </div>
                                        <span>{{ $member->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-700">
                                    {{ $member->email }}
                                </td>
                                <td class="py-3 px-4">
                                    @php
                                        $roleLabels = [
                                            'admin' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            'order_staff' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'accounts' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'delivery_staff' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        ];
                                        $label = $roleLabels[$member->role] ?? 'bg-slate-100 text-slate-800';
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $label }} capitalize">
                                        {{ str_replace('_', ' ', $member->role) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600">
                                    {{ $member->phone ?: 'N/A' }}
                                </td>
                                <td class="py-3 px-4">
                                    <x-status-badge :status="$member->status" size="xs" />
                                </td>
                                <td class="py-3 px-4 text-slate-400 text-[11px]">
                                    {{ $member->created_at->format('M d, Y') }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if($member->id !== auth()->id())
                                        <form action="{{ route('staff.destroy', $member) }}" method="POST" onsubmit="return confirm('Deactivate this staff member?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs font-semibold">
                                                Deactivate
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-400 italic">Current User</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Staff Modal -->
    <x-modal name="add-staff-modal" title="Add Team Member" maxWidth="md">
        <form action="{{ route('staff.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="e.g. Salim Mohammed" class="w-full text-xs rounded-lg border-slate-300 py-2">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address <span class="text-rose-500">*</span></label>
                <input type="email" name="email" required placeholder="salim@freshdeal.com" class="w-full text-xs rounded-lg border-slate-300 py-2">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Password <span class="text-rose-500">*</span></label>
                <input type="password" name="password" required value="password" class="w-full text-xs rounded-lg border-slate-300 py-2">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Assigned Role</label>
                <select name="role" required class="w-full text-xs rounded-lg border-slate-300 py-2">
                    <option value="admin">Admin (Full Access)</option>
                    <option value="order_staff">Order Staff (Inbox, Orders, Customers)</option>
                    <option value="accounts">Accounts (Payments, Ledger, Reports)</option>
                    <option value="delivery_staff">Delivery Staff (Deliveries, Run-sheets)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Phone Number</label>
                <input type="text" name="phone" placeholder="+971 50 xxx xxxx" class="w-full text-xs rounded-lg border-slate-300 py-2">
            </div>

            <input type="hidden" name="status" value="active">

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" @click="$dispatch('close-modal', 'add-staff-modal')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs">
                    Create Member
                </button>
            </div>
        </form>
    </x-modal>
</x-layouts.app>
