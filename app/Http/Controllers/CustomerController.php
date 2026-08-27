<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Product;
use App\Models\CustomerProductPrice;
use App\Services\CustomerService;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'business_type' => $request->get('business_type'),
            'status' => $request->get('status'),
        ];

        $customers = $this->customerService->getCustomers($filters, 15);
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();
        $totalOutstanding = Customer::sum('outstanding_balance');

        return view('customers.index', compact(
            'customers',
            'filters',
            'totalCustomers',
            'activeCustomers',
            'totalOutstanding'
        ));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'whatsapp_number' => 'required|string|max:50|unique:customers,whatsapp_number',
            'email' => 'nullable|email|max:255',
            'business_type' => 'required|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        if (!empty($validated['address'])) {
            CustomerAddress::create([
                'customer_id' => $customer->id,
                'title' => 'Primary Location',
                'address_line' => $validated['address'],
                'city' => $validated['city'] ?? 'Dubai',
                'is_default' => true,
            ]);
        }

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['addresses', 'orders.orderItems', 'payments', 'customPrices.product', 'conversations']);
        $stats = $this->customerService->getCustomerStats($customer);
        $allProducts = Product::where('status', 'active')->get();

        return view('customers.show', compact('customer', 'stats', 'allProducts'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'whatsapp_number' => 'required|string|max:50|unique:customers,whatsapp_number,' . $customer->id,
            'email' => 'nullable|email|max:255',
            'business_type' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['status'])) {
            $validated['status'] = $customer->status ?: 'active';
        }

        $customer->update($validated);

        if (!empty($validated['address'])) {
            CustomerAddress::updateOrCreate(
                ['customer_id' => $customer->id, 'is_default' => true],
                [
                    'address_line' => $validated['address'],
                    'city' => $validated['city'] ?? ($customer->city ?: 'Dubai'),
                ]
            );
        }

        return redirect()->back()
            ->with('success', "Customer {$customer->displayName} updated successfully.");
    }

    public function updateCustomPrice(Request $request, Customer $customer)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'custom_price' => 'required|numeric|min:0.01',
        ]);

        $this->customerService->setCustomPrice($customer, $request->product_id, (float)$request->custom_price);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Custom pricing updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deactivated successfully.');
    }
}
