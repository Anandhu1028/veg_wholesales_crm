<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\WhatsAppAccount;
use App\Models\Order;
use App\Models\Product;
use App\Services\ConversationService;
use App\Services\WhatsAppService;
use App\Services\DemoWhatsAppService;

class InboxController extends Controller
{
    public function __construct(
        protected ConversationService $conversationService,
        protected WhatsAppService $whatsAppService,
        protected DemoWhatsAppService $demoWhatsAppService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'filter' => $request->get('filter', 'all'),
            'whatsapp_account_id' => $request->get('whatsapp_account_id'),
        ];

        $conversations = $this->conversationService->getConversations($filters, 50);

        // Determine active conversation
        $activeConversationId = $request->get('conversation_id');
        $activeConversation = null;

        if ($activeConversationId) {
            $activeConversation = Conversation::with(['customer.addresses', 'customer.orders.orderItems', 'customer.customPrices', 'whatsappAccount', 'messages.senderUser', 'user'])
                ->find($activeConversationId);
        }

        if (!$activeConversation && $conversations->isNotEmpty()) {
            $activeConversation = Conversation::with(['customer.addresses', 'customer.orders.orderItems', 'customer.customPrices', 'whatsappAccount', 'messages.senderUser', 'user'])
                ->find($conversations->first()->id);
        }

        if ($activeConversation) {
            $this->conversationService->markAsRead($activeConversation);
        }

        $allCustomers = Customer::where('status', '!=', 'blocked')->orderBy('name')->get();
        $whatsappAccounts = WhatsAppAccount::all();
        $allProducts = Product::where('status', 'active')->get();

        // Customer details for the active conversation
        $customer = $activeConversation ? $activeConversation->customer : null;
        $currentOrder = null;
        $previousOrders = collect();

        if ($customer) {
            $currentOrder = $customer->orders()->whereIn('status', ['New', 'Confirmed', 'Processing', 'Ready', 'Out for Delivery'])->latest()->first();
            $previousOrdersQuery = $customer->orders();
            if ($currentOrder) {
                $previousOrdersQuery->where('id', '!=', $currentOrder->id);
            }
            $previousOrders = $previousOrdersQuery->take(5)->get();
        }

        $unreadCount = Conversation::sum('unread_count');

        return view('inbox.index', compact(
            'conversations',
            'activeConversation',
            'customer',
            'currentOrder',
            'previousOrders',
            'allCustomers',
            'whatsappAccounts',
            'allProducts',
            'filters',
            'unreadCount'
        ));
    }

    /**
     * Send manual staff message in active chat
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $this->whatsAppService->sendMessage($conversation, $request->message);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'conversation_id' => $conversation->id,
            ]);
        }

        return redirect()->route('inbox', ['conversation_id' => $conversation->id])
            ->with('success', 'Message sent successfully.');
    }

    /**
     * Simulate incoming customer WhatsApp message modal submit
     */
    public function simulateIncoming(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable',
            'new_customer_name' => 'nullable|string',
            'new_customer_phone' => 'nullable|string',
            'whatsapp_account_id' => 'nullable|exists:whatsapp_accounts,id',
            'message' => 'required|string',
        ]);

        $payload = [
            'body' => $request->message,
            'whatsapp_account_id' => $request->whatsapp_account_id,
        ];

        if ($request->filled('customer_id') && $request->customer_id !== 'new') {
            $payload['customer_id'] = $request->customer_id;
        } else {
            $payload['customer_name'] = $request->new_customer_name ?: 'New Customer';
            $payload['whatsapp_number'] = $request->new_customer_phone ?: '+971 50 ' . rand(100, 999) . ' ' . rand(1000, 9999);
        }

        $incomingMsg = $this->demoWhatsAppService->handleIncomingMessage($payload);
        $conversationId = $incomingMsg->conversation_id;

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'conversation_id' => $conversationId,
            ]);
        }

        return redirect()->route('inbox', ['conversation_id' => $conversationId])
            ->with('success', 'WhatsApp message simulated successfully.');
    }

    /**
     * Toggle Human Handoff / Resume Bot Automation
     */
    public function toggleHandoff(Request $request, Conversation $conversation)
    {
        $enable = $request->boolean('enable', $conversation->status !== 'human_required');
        $this->conversationService->toggleHumanHandoff($conversation, $enable);

        $msg = $enable ? 'Conversation switched to Human Staff mode.' : 'Bot automation resumed for this conversation.';

        return redirect()->route('inbox', ['conversation_id' => $conversation->id])
            ->with('success', $msg);
    }

    /**
     * Toggle Star
     */
    public function toggleStar(Conversation $conversation)
    {
        $isStarred = $this->conversationService->toggleStar($conversation);

        return redirect()->route('inbox', ['conversation_id' => $conversation->id])
            ->with('success', $isStarred ? 'Conversation starred.' : 'Conversation unstarred.');
    }
}
