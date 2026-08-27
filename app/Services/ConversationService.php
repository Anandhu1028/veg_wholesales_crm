<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ConversationService
{
    public function getConversations(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Conversation::with(['customer', 'whatsappAccount', 'user'])
            ->orderBy('last_message_at', 'desc');

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('business_name', 'like', $search)
                  ->orWhere('whatsapp_number', 'like', $search);
            });
        }

        if (!empty($filters['filter'])) {
            switch ($filters['filter']) {
                case 'unread':
                    $query->where('unread_count', '>', 0);
                    break;
                case 'orders':
                    $query->whereHas('orders');
                    break;
                case 'waiting':
                    $query->where('bot_state', 'CONFIRM_ORDER');
                    break;
                case 'human':
                    $query->where('status', 'human_required');
                    break;
            }
        }

        if (!empty($filters['whatsapp_account_id'])) {
            $query->where('whatsapp_account_id', $filters['whatsapp_account_id']);
        }

        return $query->paginate($perPage);
    }

    public function markAsRead(Conversation $conversation): void
    {
        $conversation->update(['unread_count' => 0]);
        $conversation->messages()->where('is_read', false)->update(['is_read' => true]);
    }

    public function toggleHumanHandoff(Conversation $conversation, bool $enable): void
    {
        if ($enable) {
            $conversation->update([
                'status' => 'human_required',
                'bot_state' => 'HUMAN_HANDOFF',
            ]);
        } else {
            $conversation->update([
                'status' => 'bot_active',
                'bot_state' => 'WELCOME',
            ]);
        }
    }

    public function assignStaff(Conversation $conversation, ?User $user): void
    {
        $conversation->update([
            'user_id' => $user ? $user->id : null,
        ]);
    }

    public function toggleStar(Conversation $conversation): bool
    {
        $conversation->update([
            'is_starred' => !$conversation->is_starred,
        ]);
        return $conversation->is_starred;
    }
}
