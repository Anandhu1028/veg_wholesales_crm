<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Message;
use App\Models\WhatsAppAccount;
use App\Services\Contracts\WhatsAppServiceInterface;
use Illuminate\Support\Str;

class DemoWhatsAppService implements WhatsAppServiceInterface
{
    public function __construct(
        protected DemoBotService $botService
    ) {}

    /**
     * Send an outgoing text/template message from Staff or Bot
     */
    public function sendMessage(Conversation $conversation, string $body, string $type = 'text', array $metadata = []): Message
    {
        $senderType = auth()->check() ? 'staff' : 'bot';
        $senderUserId = auth()->id();

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => $senderType,
            'sender_user_id' => $senderUserId,
            'body' => $body,
            'message_type' => $type,
            'metadata' => $metadata,
            'is_read' => true,
        ]);

        $conversation->update([
            'last_message' => Str::limit($body, 60),
            'last_message_at' => now(),
            'unread_count' => 0,
        ]);

        return $message;
    }

    /**
     * Simulate an incoming customer WhatsApp message and trigger bot processing
     */
    public function handleIncomingMessage(array $payload): Message
    {
        $customer = null;

        // 1. Resolve Customer
        if (!empty($payload['customer_id'])) {
            $customer = Customer::find($payload['customer_id']);
        } elseif (!empty($payload['whatsapp_number'])) {
            $customer = Customer::where('whatsapp_number', $payload['whatsapp_number'])->first();
        }

        if (!$customer) {
            $name = $payload['customer_name'] ?? 'New Customer';
            $phone = $payload['whatsapp_number'] ?? '+971 50 ' . rand(100, 999) . ' ' . rand(1000, 9999);

            $customer = Customer::create([
                'name' => $name,
                'business_name' => $name,
                'phone' => $phone,
                'whatsapp_number' => $phone,
                'business_type' => null,
                'address' => null,
                'city' => 'Dubai',
                'status' => 'active',
            ]);
        }

        // 2. Resolve WhatsApp Account
        $account = null;
        if (!empty($payload['whatsapp_account_id'])) {
            $account = WhatsAppAccount::find($payload['whatsapp_account_id']);
        }
        if (!$account) {
            $account = WhatsAppAccount::first() ?? WhatsAppAccount::create([
                'name' => 'WA 1',
                'phone_number' => '+971 55 125 4003',
                'provider' => 'demo',
                'status' => 'connected',
                'mode' => 'simulated',
            ]);
        }

        // 3. Resolve or Create Conversation
        $conversation = Conversation::firstOrCreate(
            [
                'customer_id' => $customer->id,
                'whatsapp_account_id' => $account->id,
            ],
            [
                'status' => 'bot_active',
                'bot_state' => 'START',
                'unread_count' => 0,
            ]
        );

        $body = trim($payload['body'] ?? $payload['message'] ?? 'Hi');

        // 4. Save Incoming Message
        $incomingMessage = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'customer',
            'body' => $body,
            'message_type' => 'text',
            'is_read' => false,
        ]);

        $conversation->increment('unread_count');
        $conversation->update([
            'last_message' => Str::limit($body, 60),
            'last_message_at' => now(),
        ]);

        // 5. Trigger Bot processing
        $this->botService->processMessage($conversation, $incomingMessage);

        return $incomingMessage;
    }

    /**
     * Provider Status
     */
    public function getStatus(): array
    {
        return [
            'mode' => 'DEMO (Simulated)',
            'provider' => 'Demo Driver',
            'is_connected' => true,
            'notice' => 'Demo Mode — WhatsApp messages are simulated for this demonstration. Real WhatsApp Cloud API integration can be connected later.'
        ];
    }
}
