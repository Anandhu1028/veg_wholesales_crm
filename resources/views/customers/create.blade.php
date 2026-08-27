<x-layouts.app :headerTitle="'Add New Customer'" :headerSubtitle="'Register wholesale vegetable buyer'">
    <div class="p-6 max-w-3xl mx-auto">
        <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                <h3 class="text-sm font-bold text-slate-900">Customer Registration</h3>
                <a href="{{ route('customers.index') }}" class="text-xs text-slate-500 hover:text-slate-900">&larr; Back to Customers</a>
            </div>

            <form action="{{ route('customers.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Business Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="business_name" value="{{ old('business_name') }}" required placeholder="e.g. Royal Fresh Mart" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Contact Person Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Tariq Ahmed" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">WhatsApp Number <span class="text-rose-500">*</span></label>
                        <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number') }}" required placeholder="+971 50 xxx xxxx" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alternate Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+971 4 xxx xxxx" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="orders@customer.ae" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Business Type</label>
                        <select name="business_type" class="w-full text-xs rounded-lg border-slate-300 py-2">
                            <option value="Wholesale">Wholesale</option>
                            <option value="Hotel">Hotel</option>
                            <option value="Restaurant">Restaurant</option>
                            <option value="Supermarket">Supermarket</option>
                            <option value="Retailer">Retailer</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Delivery Address</label>
                        <textarea name="address" rows="2" placeholder="Warehouse / Shop location for early morning produce drops" class="w-full text-xs rounded-lg border-slate-300 p-2"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Credit Limit (₹)</label>
                        <input type="number" step="1000" name="credit_limit" value="50000" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">City / Region</label>
                        <input type="text" name="city" value="Dubai" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('customers.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs">
                        Save Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
