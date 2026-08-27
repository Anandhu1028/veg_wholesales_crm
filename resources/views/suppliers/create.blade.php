<x-layouts.app :headerTitle="'Add Supplier'" :headerSubtitle="'Register farm grower or wholesale produce vendor'">
    <div class="p-6 max-w-2xl mx-auto">
        <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                <h3 class="text-sm font-bold text-slate-900">Supplier Details</h3>
                <a href="{{ route('suppliers.index') }}" class="text-xs text-slate-500 hover:text-slate-900">&larr; Back</a>
            </div>

            <form action="{{ route('suppliers.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Company / Farm Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="company_name" required placeholder="e.g. Al Ain Hydroponics" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Contact Person <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="e.g. Salim Al Marri" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Phone Number <span class="text-rose-500">*</span></label>
                        <input type="text" name="phone" required placeholder="+971 50 xxx xxxx" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" placeholder="+971 50 xxx xxxx" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" placeholder="sales@farm.ae" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Terms</label>
                        <select name="payment_terms" class="w-full text-xs rounded-lg border-slate-300 py-2">
                            <option value="Net 30">Net 30 Days</option>
                            <option value="Net 15">Net 15 Days</option>
                            <option value="Cash on Delivery">Cash on Delivery</option>
                            <option value="Advance">Advance Payment</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Farm / Warehouse Address</label>
                        <textarea name="address" rows="2" class="w-full text-xs rounded-lg border-slate-300 p-2"></textarea>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('suppliers.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs">
                        Save Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
