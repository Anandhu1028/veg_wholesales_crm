<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PurchaseOrder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\InventoryTransaction;
use App\Models\Delivery;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'sales');

        // Total sales
        $totalSales = (float)Order::where('status', '!=', 'Cancelled')->sum('total_amount');
        $totalPurchases = (float)PurchaseOrder::where('status', 'Received')->sum('total_amount');
        $grossProfit = max(0, $totalSales - $totalPurchases);
        $marginPercent = $totalSales > 0 ? round(($grossProfit / $totalSales) * 100, 1) : 0;

        // Top Customers
        $topCustomers = Customer::withCount('orders')
            ->withSum(['orders' => function ($q) {
                $q->where('status', '!=', 'Cancelled');
            }], 'total_amount')
            ->orderByDesc('orders_sum_total_amount')
            ->take(10)
            ->get();

        // Top Products
        $topProducts = OrderItem::select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_name')
            ->orderByDesc('total_revenue')
            ->take(10)
            ->get();

        // Outstanding Customers
        $outstandingCustomers = Customer::where('outstanding_balance', '>', 0)
            ->orderByDesc('outstanding_balance')
            ->get();

        // Wastage / Damage logs
        $wastageLogs = InventoryTransaction::whereIn('type', ['wastage', 'damage'])
            ->with('product')
            ->latest()
            ->get();

        // Daily Sales for the last 14 days
        $dailySales = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(id) as order_count'), DB::raw('SUM(total_amount) as total'))
            ->where('status', '!=', 'Cancelled')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc('date')
            ->take(14)
            ->get();

        return view('reports.index', compact(
            'tab',
            'totalSales',
            'totalPurchases',
            'grossProfit',
            'marginPercent',
            'topCustomers',
            'topProducts',
            'outstandingCustomers',
            'wastageLogs',
            'dailySales'
        ));
    }
}
