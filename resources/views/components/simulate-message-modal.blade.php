@php
    $customers = \App\Models\Customer::where('status', '!=', 'blocked')->orderBy('name')->get();
    $accounts = \App\Models\WhatsAppAccount::all();
@endphp

<x-modal name="simulate-whatsapp-modal" title="Simulate WhatsApp Message" maxWidth="lg">
    <form action="{{ route('inbox.simulate') }}" method="POST" x-data="{
        selectedCustomer: '{{ $customers->first()->id ?? 'new' }}',
        messageText: 'Hi',
        setQuickMessage(text) {
            this.messageText = text;
        }
    }">
        @csrf

        <div class="space-y-4">
            <!-- Simulated Banner Notice -->
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 text-xs text-emerald-800 flex items-start gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></path></svg>
                <div>
                    <strong>Simulated WhatsApp Mode:</strong> This simulates a real customer sending a message to FreshDeal WhatsApp number. The automated bot or operations desk will process it.
                </div>
            </div>

            <!-- Customer Dropdown -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Select Customer</label>
                <select
                    name="customer_id"
                    x-model="selectedCustomer"
                    class="w-full text-xs rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 py-2"
                >
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->displayName }} ({{ $c->whatsapp_number }})</option>
                    @endforeach
                    <option value="new">+ New Customer</option>
                </select>
            </div>

            <!-- New Customer Fields if 'new' selected -->
            <div x-show="selectedCustomer === 'new'" x-cloak class="space-y-2 p-3 bg-slate-50 rounded-lg border border-slate-200">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">New Customer / Business Name</label>
                    <input type="text" name="new_customer_name" placeholder="e.g. Royal Palace Restaurant" class="w-full text-xs rounded-lg border-slate-300 focus:border-emerald-500 py-1.5">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">WhatsApp Number</label>
                    <input type="text" name="new_customer_phone" placeholder="+971 50 xxx xxxx" class="w-full text-xs rounded-lg border-slate-300 focus:border-emerald-500 py-1.5">
                </div>
            </div>

            <!-- WhatsApp Number (Receiving account) -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">To WhatsApp Account</label>
                <select name="whatsapp_account_id" class="w-full text-xs rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 py-2">
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->phone_number }}) - Demo Connected</option>
                    @endforeach
                </select>
            </div>

            <!-- Quick Scenarios -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Quick Scenarios</label>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" @click="setQuickMessage('Hi')" class="px-2 py-1 bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-700 rounded text-[11px] font-medium transition-colors">
                        "Hi" (Welcome)
                    </button>
                    <button type="button" @click="setQuickMessage('Place New Order')" class="px-2 py-1 bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-700 rounded text-[11px] font-medium transition-colors">
                        "Place New Order"
                    </button>
                    <button type="button" @click="setQuickMessage('Tomato 20kg\nOnion 30kg\nPotato 50kg')" class="px-2 py-1 bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-700 rounded text-[11px] font-medium transition-colors">
                        "Tomato 20kg, Onion 30kg, Potato 50kg"
                    </button>
                    <button type="button" @click="setQuickMessage('1')" class="px-2 py-1 bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-700 rounded text-[11px] font-medium transition-colors">
                        "1" (Confirm Order)
                    </button>
                    <button type="button" @click="setQuickMessage('Repeat Previous Order')" class="px-2 py-1 bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-700 rounded text-[11px] font-medium transition-colors">
                        "Repeat Previous Order"
                    </button>
                    <button type="button" @click="setQuickMessage('Talk to Staff')" class="px-2 py-1 bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-700 rounded text-[11px] font-medium transition-colors">
                        "Talk to Staff"
                    </button>
                </div>
            </div>

            <!-- Message Body -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Customer Message Content</label>
                <textarea
                    name="message"
                    rows="3"
                    x-model="messageText"
                    required
                    placeholder="Type what customer is sending via WhatsApp..."
                    class="w-full text-xs rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 p-2.5 font-mono"
                ></textarea>
            </div>
        </div>

        <div class="mt-5 flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
            <button
                type="button"
                @click="$dispatch('close-modal', 'simulate-whatsapp-modal')"
                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
            >
                Cancel
            </button>
            <button
                type="submit"
                class="px-4 py-2 text-xs font-semibold text-white bg-[#25D366] hover:bg-[#1faa52] rounded-lg shadow-xs transition-colors flex items-center gap-1.5"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                <span>Send WhatsApp Message</span>
            </button>
        </div>
    </form>
</x-modal>
