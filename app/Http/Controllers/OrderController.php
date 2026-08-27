<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'search'         => $request->get('search'),
            'status'         => $request->get('status', 'all'),
            'source'         => $request->get('source'),
            'payment_status' => $request->get('payment_status'),
            'customer_id'    => $request->get('customer_id'),
        ];

        $orders = $this->orderService->getOrders($filters, 15);

        // Counts for status tabs
        $statusCounts = [
            'all'              => Order::count(),
            'New'              => Order::where('status', 'New')->count(),
            'Confirmed'        => Order::where('status', 'Confirmed')->count(),
            'Processing'       => Order::where('status', 'Processing')->count(),
            'Ready'            => Order::where('status', 'Ready')->count(),
            'Out for Delivery' => Order::where('status', 'Out for Delivery')->count(),
            'Delivered'        => Order::where('status', 'Delivered')->count(),
            'Cancelled'        => Order::where('status', 'Cancelled')->count(),
        ];

        return view('orders.index', compact('orders', 'filters', 'statusCounts'));
    }

    public function create()
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $products  = Product::where('status', 'active')->orderBy('name')->get();

        return view('orders.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'            => 'required|exists:customers,id',
            'delivery_date'          => 'nullable|date',
            'time_slot'              => 'nullable|string',
            'payment_method'         => 'required|string',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.quantity'       => 'required|numeric|min:0.1',
        ]);

        $order = $this->orderService->createOrder($request->all(), Auth::id());

        return redirect()->route('orders.show', $order)
            ->with('success', "Order #{$order->order_number} created successfully.");
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'orderItems.product', 'delivery', 'payments.receiver', 'creator', 'conversation']);

        return view('orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:New,Confirmed,Processing,Ready,Out for Delivery,Delivered,Cancelled',
        ]);

        $this->orderService->updateStatus($order, $request->status, Auth::id());

        return redirect()->route('orders.show', $order)
            ->with('success', "Order status updated to {$request->status}.");
    }

    public function repeat(Order $order)
    {
        $newOrder = $this->orderService->repeatOrder($order, null, Auth::id());

        return redirect()->route('orders.show', $newOrder)
            ->with('success', "Order repeated successfully as #{$newOrder->order_number}.");
    }

    /**
     * Record payment directly from Order show page (for COD after delivery)
     */
    public function recordPayment(Request $request, Order $order)
    {
        $request->validate([
            'amount'           => 'required|numeric|min:0.01',
            'payment_method'   => 'required|string',
            'reference_number' => 'nullable|string|max:255',
            'payment_date'     => 'required|date',
            'notes'            => 'nullable|string|max:500',
        ]);

        $this->paymentService->recordPaymentForOrder($order, [
            'amount'           => $request->amount,
            'payment_method'   => $request->payment_method,
            'reference_number' => $request->reference_number,
            'payment_date'     => $request->payment_date,
            'notes'            => $request->notes,
        ], Auth::id());

        return redirect()->route('orders.show', $order)
            ->with('success', "Payment of ₹" . number_format($request->amount, 2) . " recorded successfully.");
    }
}
