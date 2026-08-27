<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\WhatsAppAccount;
use App\Models\Message;
use App\Models\ActivityLog;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $connectedNumbers  = WhatsAppAccount::where('status', 'connected')->count();
        $totalConversations = Conversation::count();
        $newOrders         = Order::where('status', 'New')->count();
        $pendingOrders     = Order::whereIn('status', ['New', 'Confirmed', 'Processing', 'Ready', 'Out for Delivery'])->count();
        $deliveredToday    = Order::where('status', 'Delivered')->whereDate('updated_at', today())->count();
        $pendingPayments   = (float) Customer::sum('outstanding_balance');

        // ── Payment stats for today ──────────────────────────────────────
        $todaysSales       = (float) Order::whereDate('created_at', today())
                                          ->where('status', '!=', 'Cancelled')
                                          ->sum('total_amount');

        $paidToday         = (float) Payment::whereIn('payment_status', ['Paid', 'Completed'])
                                             ->whereDate('payment_date', today())
                                             ->sum('amount');

        $pendingToday      = max(0, $todaysSales - $paidToday);

        $codOrdersToday    = Order::whereDate('created_at', today())
                                  ->where('payment_method', 'COD')->count();

        $creditOrdersToday = Order::whereDate('created_at', today())
                                  ->where('payment_method', 'Credit')->count();

        // Recent Orders (the ONE shared Order model — same records in Inbox, Orders, Customer)
        $recentOrders = Order::with(['customer', 'orderItems'])
            ->latest()
            ->take(8)
            ->get();

        // Recent WhatsApp Activity
        $recentActivities = Message::with(['conversation.customer', 'conversation.whatsappAccount'])
            ->latest()
            ->take(6)
            ->get();

        // Weekly Sales Data for Chart
        $salesDays    = [];
        $salesAmounts = [];
        for ($i = 6; $i >= 0; $i--) {
            $date      = now()->subDays($i);
            $dayName   = $date->format('D');
            $dailySum  = (float) Order::whereDate('created_at', $date->toDateString())
                ->where('status', '!=', 'Cancelled')
                ->sum('total_amount');

            $salesDays[]    = $dayName;
            $salesAmounts[] = $dailySum;
        }

        // Fallback default chart numbers if fresh install
        if (array_sum($salesAmounts) == 0) {
            $salesAmounts = [18500, 24200, 19800, 31400, 28600, 36500, 22100];
        }

        $unreadCount = Conversation::sum('unread_count');

        return view('dashboard.index', compact(
            'connectedNumbers',
            'totalConversations',
            'newOrders',
            'pendingOrders',
            'deliveredToday',
            'pendingPayments',
            'todaysSales',
            'paidToday',
            'pendingToday',
            'codOrdersToday',
            'creditOrdersToday',
            'recentOrders',
            'recentActivities',
            'salesDays',
            'salesAmounts',
            'unreadCount'
        ));
    }
}
