<?php

namespace App\Services\Contracts;

use App\Models\Conversation;
use App\Models\Message;

interface WhatsAppServiceInterface
{
    /**
     * Send an outgoing text or template message
     */
    public function sendMessage(Conversation $conversation, string $body, string $type = 'text', array $metadata = []): Message;

    /**
     * Handle incoming message webhook or simulation
     */
    public function handleIncomingMessage(array $payload): Message;

    /**
     * Get provider status
     */
    public function getStatus(): array;
}
