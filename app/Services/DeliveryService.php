<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\ActivityLog;

class DeliveryService
{
    public function updateDeliveryStatus(Delivery $delivery, string $status, ?string $notes = null, ?int $userId = null): Delivery
    {
        $oldStatus = $delivery->status;
        $updates = ['status' => $status];

        if ($notes) {
            $updates['delivery_notes'] = $notes;
        }

        if ($status === 'Delivered') {
            $updates['delivered_at'] = now();
        }

        $delivery->update($updates);

        // Sync order status if needed
        if ($delivery->order) {
            if ($status === 'Delivered') {
                app(OrderService::class)->updateStatus($delivery->order, 'Delivered', $userId);
            } elseif ($status === 'Out for Delivery') {
                $delivery->order->update(['status' => 'Out for Delivery']);
            }
        }

        ActivityLog::create([
            'user_id' => $userId,
            'action' => 'delivery_status_updated',
            'description' => "Delivery for Order {$delivery->order?->order_number} marked as {$status}",
            'subject_type' => Delivery::class,
            'subject_id' => $delivery->id,
        ]);

        return $delivery;
    }
}
