<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\Contracts\WhatsAppServiceInterface;

class WhatsAppService implements WhatsAppServiceInterface
{
    protected WhatsAppServiceInterface $driver;

    public function __construct(DemoWhatsAppService $demoDriver)
    {
        // Future: resolve dynamically based on config('services.whatsapp.driver') e.g. meta_cloud vs demo
        $this->driver = $demoDriver;
    }

    public function sendMessage(Conversation $conversation, string $body, string $type = 'text', array $metadata = []): Message
    {
        return $this->driver->sendMessage($conversation, $body, $type, $metadata);
    }

    public function handleIncomingMessage(array $payload): Message
    {
        return $this->driver->handleIncomingMessage($payload);
    }

    public function getStatus(): array
    {
        return $this->driver->getStatus();
    }
}
