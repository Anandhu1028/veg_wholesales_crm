<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WhatsAppAccount;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Services\WhatsAppService;

class WhatsAppController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsAppService
    ) {}

    public function index()
    {
        $accounts = WhatsAppAccount::all();
        $totalConversations = Conversation::count();
        $messagesToday = Message::whereDate('created_at', now()->today())->count();
        $ordersToday = Order::where('source', 'WhatsApp')->whereDate('created_at', now()->today())->count();
        $driverStatus = $this->whatsAppService->getStatus();

        return view('whatsapp.index', compact(
            'accounts',
            'totalConversations',
            'messagesToday',
            'ordersToday',
            'driverStatus'
        ));
    }

    public function update(Request $request, WhatsAppAccount $whatsapp)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'phone_number' => 'required|string',
            'provider' => 'required|string',
            'status' => 'required|string',
            'mode' => 'required|string',
            'webhook_url' => 'nullable|url',
            'api_key' => 'nullable|string',
            'phone_number_id' => 'nullable|string',
        ]);

        $whatsapp->update($validated);

        return back()->with('success', 'WhatsApp account configuration saved.');
    }
}
