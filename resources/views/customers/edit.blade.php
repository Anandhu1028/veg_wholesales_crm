<x-layouts.app :headerTitle="'Edit Customer: ' . $customer->displayName" :headerSubtitle="'Update business information, contact and credit limit'">
    <div class="p-6 max-w-3xl mx-auto">
        <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-xs">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
                <h3 class="text-sm font-bold text-slate-900">Edit Customer Information</h3>
                <a href="{{ route('customers.show', $customer) }}" class="text-xs text-slate-500 hover:text-slate-900">&larr; Back to Profile</a>
            </div>

            <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Business Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="business_name" value="{{ old('business_name', $customer->business_name) }}" required class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Contact Person Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">WhatsApp Number <span class="text-rose-500">*</span></label>
                        <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $customer->whatsapp_number) }}" required class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Business Type</label>
                        <select name="business_type" class="w-full text-xs rounded-lg border-slate-300 py-2">
                            <option value="Wholesale" {{ $customer->business_type === 'Wholesale' ? 'selected' : '' }}>Wholesale</option>
                            <option value="Hotel" {{ $customer->business_type === 'Hotel' ? 'selected' : '' }}>Hotel</option>
                            <option value="Restaurant" {{ $customer->business_type === 'Restaurant' ? 'selected' : '' }}>Restaurant</option>
                            <option value="Supermarket" {{ $customer->business_type === 'Supermarket' ? 'selected' : '' }}>Supermarket</option>
                            <option value="Retailer" {{ $customer->business_type === 'Retailer' ? 'selected' : '' }}>Retailer</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
                        <select name="status" class="w-full text-xs rounded-lg border-slate-300 py-2">
                            <option value="active" {{ $customer->status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $customer->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="blocked" {{ $customer->status === 'blocked' ? 'selected' : '' }}>Blocked</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Credit Limit (₹)</label>
                        <input type="number" step="1000" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" class="w-full text-xs rounded-lg border-slate-300 py-2">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Delivery Address</label>
                        <textarea name="address" rows="2" class="w-full text-xs rounded-lg border-slate-300 p-2">{{ old('address', $customer->address) }}</textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Internal Notes</label>
                        <textarea name="notes" rows="2" class="w-full text-xs rounded-lg border-slate-300 p-2">{{ old('notes', $customer->notes) }}</textarea>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('customers.show', $customer) }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs">
                        Update Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
