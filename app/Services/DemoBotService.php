<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Delivery;
use App\Models\Payment;
use App\Models\ActivityLog;
use Illuminate\Support\Str;

class DemoBotService
{
    /**
     * Process an incoming customer message and return bot responses
     */
    public function processMessage(Conversation $conversation, Message $incomingMessage): ?Message
    {
        // If human handoff is active, do not auto-respond unless explicitly instructed
        if ($conversation->status === 'human_required') {
            return null;
        }

        $customer = $conversation->customer;
        $body = trim($incomingMessage->body);
        $lower = strtolower($body);
        $state = $conversation->bot_state ?: 'START';
        $context = $conversation->bot_context ?: [];

        // Check global intent: Talk to staff
        if (preg_match('/(talk to staff|human|support|agent|representative|help|call me)/i', $lower)) {
            $conversation->update([
                'status' => 'human_required',
                'bot_state' => 'HUMAN_HANDOFF',
            ]);

            return $this->sendBotMessage(
                $conversation,
                "👤 *FreshDeal Support Assigned*\n\nOur operations and order desk team has been notified. A staff member will reply to you directly here shortly.",
                'interactive',
                ['action' => 'human_handoff']
            );
        }

        // =========================================================================
        // 1. ONBOARDING STATE 1: Collecting Business Type
        // =========================================================================
        if ($state === 'COLLECT_BUSINESS_TYPE') {
            $cleanType = $this->cleanBusinessType($body);

            if (empty($cleanType)) {
                return $this->sendBotMessage(
                    $conversation,
                    "🏪 Please select or type your Business Type:\n\n• Wholesale\n• Retail Shop\n• Hotel / Restaurant\n• Supermarket\n• Catering\n• Other",
                    'interactive',
                    ['quick_replies' => $this->getBusinessTypeQuickReplies()]
                );
            }

            // Save immediately to customer in database
            $customer->update(['business_type' => $cleanType]);

            // Next check if address is missing
            if (empty(trim($customer->address ?? ''))) {
                $conversation->update(['bot_state' => 'COLLECT_ADDRESS']);

                return $this->sendBotMessage(
                    $conversation,
                    "📍 Please send your complete delivery address."
                );
            }

            // Profile complete!
            return $this->completeOnboardingAndSendWelcome($conversation, $customer);
        }

        // =========================================================================
        // 2. ONBOARDING STATE 2: Collecting Address
        // =========================================================================
        if ($state === 'COLLECT_ADDRESS') {
            $address = trim($body);

            if (strlen($address) < 5) {
                return $this->sendBotMessage(
                    $conversation,
                    "Please send your complete delivery address so we can arrange delivery."
                );
            }

            // Save immediately to customer in database
            $customer->update(['address' => $address]);

            // Also ensure CustomerAddress record exists
            CustomerAddress::updateOrCreate(
                ['customer_id' => $customer->id, 'is_default' => true],
                ['address_line' => $address, 'city' => $customer->city ?: 'Dubai']
            );

            // Profile complete!
            return $this->completeOnboardingAndSendWelcome($conversation, $customer);
        }

        // =========================================================================
        // 3. Greeting / Reset / Start Fresh
        // =========================================================================
        if (in_array($lower, ['hi', 'hello', 'hey', 'start', 'menu', 'restart', 'namaste', 'halo', 'good morning', 'good evening'])) {
            // Check if profile is incomplete:
            if (empty(trim($customer->business_type ?? ''))) {
                $conversation->update(['bot_state' => 'COLLECT_BUSINESS_TYPE']);

                $msg = "👋 *Welcome to FreshDeal Wholesale Vegetables!*\n\n" .
                       "Before we continue, we need a few details about your business.\n\n" .
                       "🏪 *What is your Business Type?*";

                return $this->sendBotMessage($conversation, $msg, 'interactive', [
                    'quick_replies' => $this->getBusinessTypeQuickReplies()
                ]);
            }

            if (empty(trim($customer->address ?? ''))) {
                $conversation->update(['bot_state' => 'COLLECT_ADDRESS']);

                return $this->sendBotMessage(
                    $conversation,
                    "📍 Please send your complete delivery address."
                );
            }

            // Existing recognized customer with complete profile
            $conversation->update([
                'bot_state' => 'WELCOME',
                'bot_context' => [],
            ]);

            $greetingName = $customer->displayName;
            $msg = "👋 *Welcome back, {$greetingName}!* \n\nHow can we help you today?";

            return $this->sendBotMessage($conversation, $msg, 'interactive', [
                'quick_replies' => [
                    ['title' => '🛒 Place New Order', 'payload' => 'place_order'],
                    ['title' => '🔁 Repeat Last Order', 'payload' => 'repeat_order'],
                    ['title' => '👤 Talk to Staff', 'payload' => 'talk_to_staff'],
                ]
            ]);
        }

        // =========================================================================
        // 4. State Machine Processing (Orders, Payments, Cart)
        // =========================================================================
        switch ($state) {
            case 'START':
                // Check if profile needs onboarding
                if (empty(trim($customer->business_type ?? ''))) {
                    $conversation->update(['bot_state' => 'COLLECT_BUSINESS_TYPE']);

                    return $this->sendBotMessage(
                        $conversation,
                        "👋 *Welcome to FreshDeal Wholesale Vegetables!*\n\nBefore we continue, we need a few details about your business.\n\n🏪 *What is your Business Type?*",
                        'interactive',
                        ['quick_replies' => $this->getBusinessTypeQuickReplies()]
                    );
                }

                if (empty(trim($customer->address ?? ''))) {
                    $conversation->update(['bot_state' => 'COLLECT_ADDRESS']);
                    return $this->sendBotMessage($conversation, "📍 Please send your complete delivery address.");
                }

                // If customer directly sent items
                $parsedItems = $this->parseVegetableItems($body, $customer);
                if (!empty($parsedItems)) {
                    return $this->handleDraftOrderSummary($conversation, $customer, $parsedItems);
                }

                $conversation->update(['bot_state' => 'WELCOME']);
                return $this->sendBotMessage($conversation, "👋 Welcome back, {$customer->displayName}!\n\nHow can we help you today?", 'interactive', [
                    'quick_replies' => [
                        ['title' => '🛒 Place New Order', 'payload' => 'place_order'],
                        ['title' => '🔁 Repeat Last Order', 'payload' => 'repeat_order'],
                        ['title' => '👤 Talk to Staff', 'payload' => 'talk_to_staff'],
                    ]
                ]);

            case 'WELCOME':
            case 'ORDER_SELECTION':
            case 'COMPLETED':
            case 'ORDER_CREATED':
                if (preg_match('/(repeat|repeat last order|repeat previous order)/i', $lower) || ($lower === '2' && $state === 'WELCOME')) {
                    return $this->handleRepeatOrderFlow($conversation, $customer);
                }

                if (preg_match('/(place|new order|place new order|order|buy|vegetables)/i', $lower) || ($lower === '1' && $state === 'WELCOME')) {
                    $conversation->update([
                        'bot_state' => 'COLLECT_ORDER',
                        'bot_context' => [],
                    ]);

                    return $this->sendBotMessage(
                        $conversation,
                        "📝 *Place New Wholesale Order*\n\nPlease list the vegetables and quantities you need.\n\n*Example:*\nTomato 20kg\nOnion 30kg\nPotato 50kg\n\n_You can type multiple items on separate lines or comma-separated._"
                    );
                }

                // If customer directly sent items without choosing menu
                $parsedItems = $this->parseVegetableItems($body, $customer);
                if (!empty($parsedItems)) {
                    return $this->handleDraftOrderSummary($conversation, $customer, $parsedItems);
                }

                return $this->sendBotMessage($conversation, "Please choose an option:\n\n1️⃣ *Place New Order*\n2️⃣ *Repeat Previous Order*\n3️⃣ *Talk to Staff*", 'interactive', [
                    'quick_replies' => [
                        ['title' => '🛒 Place New Order', 'payload' => 'place_order'],
                        ['title' => '🔁 Repeat Last Order', 'payload' => 'repeat_order'],
                        ['title' => '👤 Talk to Staff', 'payload' => 'talk_to_staff'],
                    ]
                ]);

            case 'COLLECT_ORDER':
                $parsedItems = $this->parseVegetableItems($body, $customer);
                if (empty($parsedItems)) {
                    return $this->sendBotMessage(
                        $conversation,
                        "⚠️ We couldn't recognize vegetable items in your message.\n\nPlease provide items like:\n*Tomato 20kg*\n*Onion 30kg*\n*Potato 50kg*\n\nAvailable products: Tomato, Onion, Potato, Carrot, Beans, Cabbage, Cauliflower, Cucumber, Green Chilli, Ginger, Garlic."
                    );
                }

                return $this->handleDraftOrderSummary($conversation, $customer, $parsedItems);

            case 'SELECT_PAYMENT_METHOD':
            case 'CONFIRM_ORDER':
                // 1. Pay Now Selection
                if (preg_match('/(pay now|1|💳 pay now|card|upi|online)/i', $lower) && !preg_match('/place|credit|cod/i', $lower)) {
                    return $this->handlePayNowPrompt($conversation, $customer);
                }

                // 2. Cash on Delivery Selection
                if (preg_match('/(cash on delivery|cod|2|🚚 cash on delivery)/i', $lower) && !preg_match('/pay now|credit/i', $lower)) {
                    return $this->handleCODPrompt($conversation, $customer);
                }

                // 3. Credit Account Selection
                if (preg_match('/(credit|credit account|3|🧾 credit account)/i', $lower) && !preg_match('/pay now|cod/i', $lower)) {
                    return $this->handleCreditPrompt($conversation, $customer);
                }

                // 4. Confirmations
                if (preg_match('/(simulate successful payment|pay|payment done|paid|confirm pay now)/i', $lower)) {
                    return $this->createOrderWithPayment($conversation, $customer, 'Pay Now');
                }

                if (preg_match('/(confirm cod order|confirm cod|yes cod)/i', $lower)) {
                    return $this->createOrderWithPayment($conversation, $customer, 'COD');
                }

                if (preg_match('/(place order on credit|confirm credit|yes credit)/i', $lower)) {
                    return $this->createOrderWithPayment($conversation, $customer, 'Credit');
                }

                if (in_array($lower, ['edit', 'edit order', 'change', 'modify'])) {
                    $conversation->update(['bot_state' => 'COLLECT_ORDER']);
                    return $this->sendBotMessage($conversation, "Please send your updated vegetable list and quantities.");
                }

                if (in_array($lower, ['cancel', 'stop', 'no'])) {
                    $conversation->update(['bot_state' => 'START', 'bot_context' => []]);
                    return $this->sendBotMessage($conversation, "❌ Order cancelled. Reply *Hi* anytime to start again.");
                }

                // Re-parsing items if re-sent
                $parsedItems = $this->parseVegetableItems($body, $customer);
                if (!empty($parsedItems)) {
                    return $this->handleDraftOrderSummary($conversation, $customer, $parsedItems);
                }

                return $this->sendPaymentMethodOptions($conversation, $customer, $context['total_amount'] ?? 0);

            case 'PAYMENT_PENDING_SIMULATION':
                if (preg_match('/(simulate successful payment|pay|payment done|paid|yes|1|confirm)/i', $lower)) {
                    return $this->createOrderWithPayment($conversation, $customer, 'Pay Now');
                } elseif (preg_match('/(change|cancel|other)/i', $lower)) {
                    return $this->sendPaymentMethodOptions($conversation, $customer, $context['total_amount'] ?? 0);
                }
                break;

            case 'COD_PENDING_CONFIRMATION':
                if (preg_match('/(confirm cod order|confirm|yes|1|ok)/i', $lower)) {
                    return $this->createOrderWithPayment($conversation, $customer, 'COD');
                } elseif (preg_match('/(change|cancel)/i', $lower)) {
                    return $this->sendPaymentMethodOptions($conversation, $customer, $context['total_amount'] ?? 0);
                }
                break;

            case 'CREDIT_PENDING_CONFIRMATION':
                if (preg_match('/(place order on credit|confirm|yes|1|ok)/i', $lower)) {
                    return $this->createOrderWithPayment($conversation, $customer, 'Credit');
                } elseif (preg_match('/(change|cancel)/i', $lower)) {
                    return $this->sendPaymentMethodOptions($conversation, $customer, $context['total_amount'] ?? 0);
                }
                break;

            default:
                $conversation->update(['bot_state' => 'WELCOME']);
                return $this->sendBotMessage($conversation, "👋 Hello! Reply *Hi* or *Place New Order* to order fresh wholesale vegetables.");
        }

        return null;
    }

    /**
     * Clean and normalize business type string (handles emoji prefixes from buttons)
     */
    protected function cleanBusinessType(string $input): string
    {
        $input = trim($input);
        $clean = preg_replace('/^[\x{1F300}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\s]+/u', '', $input);
        return trim($clean ?: $input);
    }

    /**
     * Business type options for quick reply buttons
     */
    protected function getBusinessTypeQuickReplies(): array
    {
        return [
            ['title' => 'Wholesale', 'payload' => 'Wholesale'],
            ['title' => 'Retail Shop', 'payload' => 'Retail Shop'],
            ['title' => 'Hotel / Restaurant', 'payload' => 'Hotel / Restaurant'],
            ['title' => 'Supermarket', 'payload' => 'Supermarket'],
            ['title' => 'Catering', 'payload' => 'Catering'],
            ['title' => 'Other', 'payload' => 'Other'],
        ];
    }

    /**
     * Complete onboarding and send final confirmation with main menu
     */
    protected function completeOnboardingAndSendWelcome(Conversation $conversation, Customer $customer): Message
    {
        $conversation->update([
            'bot_state' => 'WELCOME',
            'bot_context' => [],
        ]);

        $msg = "Thank you! ✅\n\n" .
               "Your details have been saved.\n\n" .
               "*Business Type:*\n{$customer->business_type}\n\n" .
               "*Delivery Address:*\n{$customer->address}\n\n" .
               "How can we help you today?";

        return $this->sendBotMessage($conversation, $msg, 'interactive', [
            'quick_replies' => [
                ['title' => '🛒 Place New Order', 'payload' => 'place_order'],
                ['title' => '🔁 Repeat Last Order', 'payload' => 'repeat_order'],
                ['title' => '👤 Talk to Staff', 'payload' => 'talk_to_staff'],
            ]
        ]);
    }

    /**
     * Handle Repeat Order Flow
     */
    protected function handleRepeatOrderFlow(Conversation $conversation, Customer $customer): Message
    {
        $lastOrder = Order::where('customer_id', $customer->id)
            ->where('status', '!=', 'Cancelled')
            ->with('orderItems')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastOrder || $lastOrder->orderItems->isEmpty()) {
            $conversation->update(['bot_state' => 'COLLECT_ORDER']);
            return $this->sendBotMessage(
                $conversation,
                "ℹ️ No previous orders found for your account.\n\nPlease list the vegetables and quantities you need to place a new order (e.g. *Tomato 20kg, Onion 30kg*)."
            );
        }

        // Re-calculate prices with current customer rates
        $items = [];
        $subtotal = 0;

        foreach ($lastOrder->orderItems as $prevItem) {
            $product = Product::find($prevItem->product_id);
            $unitPrice = $product ? $customer->getProductPrice($product) : (float)$prevItem->unit_price;
            $lineTotal = round($unitPrice * (float)$prevItem->quantity, 2);
            $subtotal += $lineTotal;

            $items[] = [
                'product_id' => $product ? $product->id : $prevItem->product_id,
                'name' => $product ? $product->name : $prevItem->product_name,
                'unit' => $prevItem->unit ?: 'kg',
                'quantity' => (float)$prevItem->quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $lineTotal,
            ];
        }

        $deliveryCharge = 0.00;
        $total = $subtotal + $deliveryCharge;

        $conversation->update([
            'bot_state' => 'SELECT_PAYMENT_METHOD',
            'bot_context' => [
                'source' => 'Repeat Order',
                'items' => $items,
                'subtotal' => $subtotal,
                'delivery_charge' => $deliveryCharge,
                'total_amount' => $total,
                'previous_order_number' => $lastOrder->order_number,
            ]
        ]);

        $itemLines = [];
        foreach ($items as $item) {
            $qty = rtrim(rtrim(number_format($item['quantity'], 2), '0'), '.');
            $rate = number_format($item['unit_price'], 2);
            $lineSum = number_format($item['subtotal'], 2);
            $itemLines[] = "• *{$item['name']}* — {$qty} {$item['unit']} @ ₹{$rate}/{$item['unit']} = *₹{$lineSum}*";
        }

        $itemsText = implode("\n", $itemLines);
        $subtotalFormatted = number_format($subtotal, 2);
        $totalFormatted = number_format($total, 2);

        $body = "🔁 *Repeat Previous Order ({$lastOrder->order_number})*\n\n" .
                "Here are the items from your last order with updated prices:\n\n" .
                "{$itemsText}\n\n" .
                "━━━━━━━━━━━━━━━━━━━━\n" .
                "*Subtotal:* ₹{$subtotalFormatted}\n" .
                "*Delivery:* FREE\n" .
                "*Total Amount:* *₹{$totalFormatted}*\n" .
                "━━━━━━━━━━━━━━━━━━━━\n\n" .
                "How would you like to pay?";

        return $this->sendPaymentOptionsWithSummary($conversation, $customer, $body, $total);
    }

    /**
     * Handle Draft Order Summary and Prompt Payment Method Selection
     */
    protected function handleDraftOrderSummary(Conversation $conversation, Customer $customer, array $items): Message
    {
        $subtotal = array_sum(array_column($items, 'subtotal'));
        $deliveryCharge = 0.00;
        $total = $subtotal + $deliveryCharge;

        $conversation->update([
            'bot_state' => 'SELECT_PAYMENT_METHOD',
            'bot_context' => [
                'source' => 'WhatsApp',
                'items' => $items,
                'subtotal' => $subtotal,
                'delivery_charge' => $deliveryCharge,
                'total_amount' => $total,
            ]
        ]);

        $itemLines = [];
        foreach ($items as $item) {
            $qty = rtrim(rtrim(number_format($item['quantity'], 2), '0'), '.');
            $rate = number_format($item['unit_price'], 2);
            $lineSum = number_format($item['subtotal'], 2);
            $itemLines[] = "• *{$item['name']}* — {$qty} {$item['unit']} @ ₹{$rate}/{$item['unit']} = *₹{$lineSum}*";
        }

        $itemsText = implode("\n", $itemLines);
        $subtotalFormatted = number_format($subtotal, 2);
        $totalFormatted = number_format($total, 2);

        $body = "📋 *Please Confirm Your Order*\n\n" .
                "{$itemsText}\n\n" .
                "━━━━━━━━━━━━━━━━━━━━\n" .
                "*Subtotal:* ₹{$subtotalFormatted}\n" .
                "*Delivery:* FREE Wholesale Delivery\n" .
                "*Total:* *₹{$totalFormatted}*\n" .
                "━━━━━━━━━━━━━━━━━━━━\n\n" .
                "How would you like to pay?";

        return $this->sendPaymentOptionsWithSummary($conversation, $customer, $body, $total);
    }

    /**
     * Send payment options based on customer eligibility
     */
    protected function sendPaymentOptionsWithSummary(Conversation $conversation, Customer $customer, string $body, float $total): Message
    {
        $quickReplies = [
            ['title' => '💳 Pay Now', 'payload' => 'pay_now'],
            ['title' => '🚚 Cash on Delivery', 'payload' => 'cod'],
        ];

        // Check Credit Eligibility
        $availableCredit = $customer->available_credit;
        if ($customer->credit_enabled && $availableCredit >= $total) {
            $quickReplies[] = ['title' => '🧾 Credit Account', 'payload' => 'credit'];
        }

        return $this->sendBotMessage($conversation, $body, 'order_summary', [
            'type' => 'payment_selection',
            'total' => $total,
            'quick_replies' => $quickReplies,
        ]);
    }

    protected function sendPaymentMethodOptions(Conversation $conversation, Customer $customer, float $total): Message
    {
        $quickReplies = [
            ['title' => '💳 Pay Now', 'payload' => 'pay_now'],
            ['title' => '🚚 Cash on Delivery', 'payload' => 'cod'],
        ];

        if ($customer->credit_enabled && $customer->available_credit >= $total) {
            $quickReplies[] = ['title' => '🧾 Credit Account', 'payload' => 'credit'];
        }

        return $this->sendBotMessage(
            $conversation,
            "How would you like to pay for this order of *₹" . number_format($total, 2) . "*?\n\n1️⃣ *Pay Now* (Instant Settlement)\n2️⃣ *Cash on Delivery*\n" . ($customer->credit_enabled ? "3️⃣ *Credit Account* (Available: ₹" . number_format($customer->available_credit, 0) . ")" : ""),
            'interactive',
            ['quick_replies' => $quickReplies]
        );
    }

    /**
     * Handle Pay Now Prompt
     */
    protected function handlePayNowPrompt(Conversation $conversation, Customer $customer): Message
    {
        $context = $conversation->bot_context ?: [];
        $total = (float)($context['total_amount'] ?? 0);
        $totalFormatted = number_format($total, 2);

        $conversation->update(['bot_state' => 'PAYMENT_PENDING_SIMULATION']);

        $body = "💳 *Payment Summary*\n\n" .
                "*Order Total:* ₹{$totalFormatted}\n" .
                "*Payment Method:* Pay Now (UPI / Card / NetBanking)\n\n" .
                "Please complete the payment of *₹{$totalFormatted}* to confirm your order.";

        return $this->sendBotMessage($conversation, $body, 'interactive', [
            'type' => 'pay_now_prompt',
            'amount' => $total,
            'quick_replies' => [
                ['title' => '⚡ Simulate Successful Payment', 'payload' => 'simulate_payment_success'],
                ['title' => '🚚 Switch to Cash on Delivery', 'payload' => 'cod'],
                ['title' => '❌ Cancel Order', 'payload' => 'cancel'],
            ]
        ]);
    }

    /**
     * Handle Cash on Delivery Prompt
     */
    protected function handleCODPrompt(Conversation $conversation, Customer $customer): Message
    {
        $context = $conversation->bot_context ?: [];
        $total = (float)($context['total_amount'] ?? 0);
        $totalFormatted = number_format($total, 2);

        $conversation->update(['bot_state' => 'COD_PENDING_CONFIRMATION']);

        $body = "🚚 *Cash on Delivery Confirmation*\n\n" .
                "*Order Total:* ₹{$totalFormatted}\n" .
                "*Payment Method:* Cash on Delivery\n\n" .
                "Your order will be delivered and *₹{$totalFormatted}* will be collected on delivery.\n\n" .
                "Reply *1* or tap below to confirm.";

        return $this->sendBotMessage($conversation, $body, 'interactive', [
            'type' => 'cod_prompt',
            'amount' => $total,
            'quick_replies' => [
                ['title' => '✅ Confirm COD Order', 'payload' => 'confirm_cod'],
                ['title' => '💳 Switch to Pay Now', 'payload' => 'pay_now'],
                ['title' => '❌ Cancel Order', 'payload' => 'cancel'],
            ]
        ]);
    }

    /**
     * Handle Credit Account Prompt
     */
    protected function handleCreditPrompt(Conversation $conversation, Customer $customer): Message
    {
        $context = $conversation->bot_context ?: [];
        $total = (float)($context['total_amount'] ?? 0);
        $totalFormatted = number_format($total, 2);

        $availableCredit = $customer->available_credit;

        if (!$customer->credit_enabled || $availableCredit < $total) {
            $conversation->update(['bot_state' => 'SELECT_PAYMENT_METHOD']);

            $body = "⚠️ *Insufficient Credit Limit*\n\n" .
                    "*Order Total:* ₹{$totalFormatted}\n" .
                    "*Available Credit:* ₹" . number_format($availableCredit, 2) . "\n" .
                    "*Credit Limit:* ₹" . number_format($customer->credit_limit, 2) . "\n\n" .
                    "Please choose another payment method.";

            return $this->sendBotMessage($conversation, $body, 'interactive', [
                'quick_replies' => [
                    ['title' => '💳 Pay Now', 'payload' => 'pay_now'],
                    ['title' => '🚚 Cash on Delivery', 'payload' => 'cod'],
                ]
            ]);
        }

        $remainingCredit = max(0, $availableCredit - $total);

        $conversation->update(['bot_state' => 'CREDIT_PENDING_CONFIRMATION']);

        $body = "🧾 *Credit Account Confirmation*\n\n" .
                "*Order Total:* ₹{$totalFormatted}\n" .
                "*Payment Method:* Credit Account\n" .
                "*Available Credit:* ₹" . number_format($availableCredit, 2) . "\n" .
                "*Credit Remaining After Order:* ₹" . number_format($remainingCredit, 2) . "\n\n" .
                "Place order on 30-day wholesale credit account?\n\n" .
                "Reply *1* or tap below to confirm.";

        return $this->sendBotMessage($conversation, $body, 'interactive', [
            'type' => 'credit_prompt',
            'amount' => $total,
            'quick_replies' => [
                ['title' => '✅ Place Order on Credit', 'payload' => 'confirm_credit'],
                ['title' => '🚚 Switch to Cash on Delivery', 'payload' => 'cod'],
                ['title' => '💳 Switch to Pay Now', 'payload' => 'pay_now'],
            ]
        ]);
    }

    /**
     * Create real Order with selected Payment Method
     */
    protected function createOrderWithPayment(Conversation $conversation, Customer $customer, string $paymentChoice): Message
    {
        $context = $conversation->bot_context ?: [];
        $items = $context['items'] ?? [];

        if (empty($items)) {
            $conversation->update(['bot_state' => 'START']);
            return $this->sendBotMessage($conversation, "⚠️ No active order items found. Reply *Hi* to place a new order.");
        }

        // Generate next Order Number e.g. ORD-1265
        $latestOrder = Order::latest('id')->first();
        $nextNum = $latestOrder ? ($latestOrder->id + 1250) : 1251;
        $orderNumber = 'ORD-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        $subtotal = (float)($context['subtotal'] ?? array_sum(array_column($items, 'subtotal')));
        $deliveryCharge = (float)($context['delivery_charge'] ?? 0.00);
        $totalAmount = (float)($context['total_amount'] ?? ($subtotal + $deliveryCharge));
        $source = $context['source'] ?? 'WhatsApp';

        // Payment status & details based on choice
        $paymentStatus = 'Pending';
        $paymentMethod = 'Cash on Delivery';
        $paidAmount = 0.00;
        $pendingAmount = $totalAmount;

        if ($paymentChoice === 'Pay Now') {
            $paymentStatus = 'Paid';
            $paymentMethod = 'UPI';
            $paidAmount = $totalAmount;
            $pendingAmount = 0.00;
        } elseif ($paymentChoice === 'COD') {
            $paymentStatus = 'Pending';
            $paymentMethod = 'COD';
            $paidAmount = 0.00;
            $pendingAmount = $totalAmount;
        } elseif ($paymentChoice === 'Credit') {
            $paymentStatus = 'Pending';
            $paymentMethod = 'Credit';
            $paidAmount = 0.00;
            $pendingAmount = $totalAmount;

            // Increase customer outstanding balance for credit order
            $customer->increment('outstanding_balance', $totalAmount);
        }

        // Saved customer address as default delivery address
        $deliveryAddress = $customer->address ?: ($customer->city ?: 'Dubai Wholesale Market');

        // Create Order (SINGLE Order record in DB used across the system)
        $order = Order::create([
            'order_number' => $orderNumber,
            'customer_id' => $customer->id,
            'conversation_id' => $conversation->id,
            'whatsapp_account_id' => $conversation->whatsapp_account_id,
            'source' => $source,
            'status' => 'Confirmed',
            'subtotal' => $subtotal,
            'discount' => 0.00,
            'delivery_charge' => $deliveryCharge,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'pending_amount' => $pendingAmount,
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'delivery_address' => $deliveryAddress,
            'delivery_date' => now()->addDay()->toDateString(),
            'time_slot' => 'Morning (6:00 AM - 9:00 AM)',
            'notes' => 'Generated via FreshDeal WhatsApp Bot (' . $source . ' • ' . $paymentMethod . ')',
        ]);

        // Create Order Items & Reserve Inventory
        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'] ?? null,
                'product_name' => $item['name'],
                'unit' => $item['unit'] ?? 'kg',
                'quantity' => (float)$item['quantity'],
                'unit_price' => (float)$item['unit_price'],
                'subtotal' => (float)$item['subtotal'],
            ]);

            if (!empty($item['product_id'])) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->increment('reserved_quantity', (float)$item['quantity']);
                }
            }
        }

        // Create Delivery Record
        Delivery::create([
            'order_id' => $order->id,
            'driver_name' => 'Rashid Khan',
            'driver_phone' => '+971 50 882 1940',
            'vehicle_number' => 'DXB-VAN-4028',
            'delivery_date' => now()->addDay()->toDateString(),
            'time_slot' => 'Morning (6:00 AM - 9:00 AM)',
            'status' => 'Pending',
            'delivery_notes' => 'Early morning wholesale delivery. Drop-off at: ' . $deliveryAddress,
        ]);

        // If Pay Now: Create successful Payment record
        if ($paymentChoice === 'Pay Now') {
            $latestPay = Payment::latest('id')->first();
            $payNum = 'PAY-' . str_pad(($latestPay ? $latestPay->id + 4001 : 4001), 4, '0', STR_PAD_LEFT);

            Payment::create([
                'payment_number' => $payNum,
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'amount' => $totalAmount,
                'payment_method' => 'UPI',
                'payment_status' => 'Paid',
                'reference' => 'UPI-' . strtoupper(Str::random(8)),
                'reference_number' => 'UPI-' . strtoupper(Str::random(8)),
                'payment_date' => now()->toDateString(),
                'paid_at' => now(),
                'status' => 'Completed',
                'notes' => 'Instant settlement via Simulated WhatsApp Bot',
            ]);
        }

        // Reset Bot State
        $conversation->update([
            'bot_state' => 'ORDER_CREATED',
            'bot_context' => [
                'last_created_order_id' => $order->id,
                'last_created_order_number' => $orderNumber,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
            ]
        ]);

        // Log Activity
        ActivityLog::create([
            'action' => 'order_created',
            'description' => "Order {$orderNumber} (₹" . number_format($totalAmount, 2) . " • {$paymentMethod}) confirmed for {$customer->displayName}",
            'subject_type' => Order::class,
            'subject_id' => $order->id,
        ]);

        $totalFormatted = number_format($totalAmount, 2);

        // Specific Bot Message per Payment Method
        if ($paymentChoice === 'Pay Now') {
            $responseMsg = "✅ *Payment successful.*\n\n" .
                           "Order *#{$orderNumber}* has been confirmed successfully.\n\n" .
                           "💰 *Amount Paid:* ₹{$totalFormatted} (UPI / Instant)\n" .
                           "📦 *Items:* " . count($items) . " products\n" .
                           "📍 *Delivery Address:* {$deliveryAddress}\n" .
                           "🚚 *Delivery:* Tomorrow Morning (6:00 AM - 9:00 AM)\n\n" .
                           "Thank you for your order. Our warehouse team is packing your produce.";
        } elseif ($paymentChoice === 'COD') {
            $responseMsg = "🚚 *Your order #{$orderNumber} has been placed.*\n\n" .
                           "*₹{$totalFormatted}* will be collected on delivery.\n\n" .
                           "📦 *Items:* " . count($items) . " products\n" .
                           "📍 *Delivery Address:* {$deliveryAddress}\n" .
                           "🚚 *Delivery:* Tomorrow Morning (6:00 AM - 9:00 AM)\n\n" .
                           "Thank you for choosing FreshDeal.";
        } else {
            $responseMsg = "🧾 *Your order #{$orderNumber} has been placed on your credit account.*\n\n" .
                           "*₹{$totalFormatted}* has been added to your outstanding balance.\n\n" .
                           "📊 *New Outstanding Balance:* ₹" . number_format($customer->outstanding_balance, 2) . "\n" .
                           "📍 *Delivery Address:* {$deliveryAddress}\n" .
                           "🚚 *Delivery:* Tomorrow Morning (6:00 AM - 9:00 AM)\n\n" .
                           "Thank you for choosing FreshDeal.";
        }

        return $this->sendBotMessage($conversation, $responseMsg, 'interactive', [
            'action' => 'order_created',
            'order_id' => $order->id,
            'order_number' => $orderNumber,
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'quick_replies' => [
                ['title' => '📦 Track Order #' . $orderNumber, 'payload' => 'track_order'],
                ['title' => '🛒 Place Another Order', 'payload' => 'place_order'],
            ]
        ]);
    }

    /**
     * Parse natural language vegetable names and quantities
     */
    public function parseVegetableItems(string $text, Customer $customer): array
    {
        $products = Product::where('status', '!=', 'inactive')->get();
        $results = [];

        $lines = preg_split('/[\n,\+&]|\band\b/i', $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            foreach ($products as $product) {
                $pName = preg_quote($product->name, '/');

                $pattern1 = '/\b' . $pName . '\s*[:\-]?\s*(\d+(?:\.\d+)?)\s*(kg|box|crate|piece|bag|pcs)?\b/i';
                $pattern2 = '/\b(\d+(?:\.\d+)?)\s*(kg|box|crate|piece|bag|pcs)?\s*(?:of\s*)?' . $pName . '\b/i';

                if (preg_match($pattern1, $line, $m) || preg_match($pattern2, $line, $m)) {
                    $qty = (float)$m[1];
                    $unit = !empty($m[2]) ? strtolower($m[2]) : $product->unit;
                    if ($unit === 'pcs') $unit = 'piece';

                    $price = $customer->getProductPrice($product);
                    $subtotal = round($price * $qty, 2);

                    $results[$product->id] = [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'unit' => $unit ?: $product->unit,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'subtotal' => $subtotal,
                    ];
                    break;
                }
            }
        }

        return array_values($results);
    }

    /**
     * Send bot message helper
     */
    protected function sendBotMessage(Conversation $conversation, string $body, string $type = 'text', array $metadata = []): Message
    {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'bot',
            'body' => $body,
            'message_type' => $type,
            'metadata' => $metadata,
            'is_read' => true,
        ]);

        $conversation->update([
            'last_message' => Str::limit($body, 60),
            'last_message_at' => now(),
        ]);

        return $message;
    }
}
