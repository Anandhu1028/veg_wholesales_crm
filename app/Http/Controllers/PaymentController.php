<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        $query = Payment::with(['customer', 'order', 'receiver'])
            ->latest('payment_date');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $payments = $query->paginate(15);
        $customers = Customer::where('status', 'active')->orderBy('name')->get();

        // Orders with any unpaid or partially paid status for the modal dropdown
        $unpaidOrders = Order::with('customer')
            ->whereIn('payment_status', ['Unpaid', 'Pending', 'Partially Paid'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalCollected  = (float) Payment::whereIn('payment_status', ['Paid', 'Completed'])->sum('amount');
        $totalOutstanding = (float) Customer::sum('outstanding_balance');

        // Payment summary stats
        $paidToday      = (float) Payment::whereIn('payment_status', ['Paid', 'Completed'])
                                         ->whereDate('payment_date', today())
                                         ->sum('amount');
        $pendingToday   = (float) Order::whereDate('created_at', today())
                                       ->whereIn('payment_status', ['Pending', 'Unpaid', 'Partially Paid'])
                                       ->sum('pending_amount');
        $codOrdersToday = Order::whereDate('created_at', today())
                               ->where('payment_method', 'COD')->count();
        $creditOrdersToday = Order::whereDate('created_at', today())
                                  ->where('payment_method', 'Credit')->count();

        return view('payments.index', compact(
            'payments',
            'customers',
            'unpaidOrders',
            'totalCollected',
            'totalOutstanding',
            'paidToday',
            'pendingToday',
            'codOrdersToday',
            'creditOrdersToday'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'      => 'required|exists:customers,id',
            'order_id'         => 'nullable|exists:orders,id',
            'amount'           => 'required|numeric|min:0.01',
            'payment_method'   => 'required|string',
            'reference_number' => 'nullable|string|max:255',
            'payment_date'     => 'required|date',
            'notes'            => 'nullable|string|max:500',
        ]);

        $this->paymentService->recordPayment($request->all(), Auth::id());

        return redirect()->route('payments.index')
            ->with('success', 'Payment recorded and customer ledger updated successfully.');
    }
}
