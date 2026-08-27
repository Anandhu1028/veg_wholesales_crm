<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Delivery;
use App\Models\ActivityLog;
use App\Models\InventoryTransaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function getOrders(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Order::with(['customer', 'orderItems.product', 'delivery', 'creator', 'whatsappAccount'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', $search)
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', $search)
                         ->orWhere('business_name', 'like', $search)
                         ->orWhere('phone', 'like', $search);
                  });
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        return $query->paginate($perPage);
    }

    public function generateOrderNumber(): string
    {
        $latest = Order::latest('id')->first();
        $next = $latest ? ($latest->id + 1250) : 1251;
        return 'ORD-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create manual order from admin or staff form
     */
    public function createOrder(array $data, ?int $userId = null): Order
    {
        return DB::transaction(function () use ($data, $userId) {
            $customer = Customer::findOrFail($data['customer_id']);
            $orderNumber = $this->generateOrderNumber();

            $itemsData = $data['items'] ?? [];
            $subtotal = 0;

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'conversation_id' => $data['conversation_id'] ?? null,
                'whatsapp_account_id' => $data['whatsapp_account_id'] ?? null,
                'source' => $data['source'] ?? 'Manual',
                'status' => $data['status'] ?? 'New',
                'subtotal' => 0, // updated below
                'discount' => (float)($data['discount'] ?? 0),
                'delivery_charge' => (float)($data['delivery_charge'] ?? 0),
                'total_amount' => 0, // updated below
                'payment_status' => $data['payment_status'] ?? 'Unpaid',
                'payment_method' => $data['payment_method'] ?? 'Cash on Delivery',
                'delivery_address' => $data['delivery_address'] ?? $customer->address,
                'delivery_date' => $data['delivery_date'] ?? now()->addDay()->toDateString(),
                'time_slot' => $data['time_slot'] ?? 'Morning (6:00 AM - 9:00 AM)',
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($itemsData as $item) {
                if (empty($item['product_id']) || empty($item['quantity'])) continue;

                $product = Product::findOrFail($item['product_id']);
                $qty = (float)$item['quantity'];
                $unitPrice = isset($item['unit_price']) ? (float)$item['unit_price'] : $customer->getProductPrice($product);
                $lineSubtotal = round($qty * $unitPrice, 2);
                $subtotal += $lineSubtotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit' => $product->unit,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineSubtotal,
                ]);

                // Reserve inventory
                $product->increment('reserved_quantity', $qty);
            }

            $discount = (float)($data['discount'] ?? 0);
            $deliveryCharge = (float)($data['delivery_charge'] ?? 0);
            $totalAmount = max(0, $subtotal - $discount + $deliveryCharge);

            $order->update([
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
            ]);

            // Create Delivery record
            Delivery::create([
                'order_id' => $order->id,
                'driver_name' => $data['driver_name'] ?? 'Rashid Khan',
                'driver_phone' => $data['driver_phone'] ?? '+971 50 882 1940',
                'vehicle_number' => $data['vehicle_number'] ?? 'DXB-VAN-4028',
                'delivery_date' => $order->delivery_date,
                'time_slot' => $order->time_slot,
                'status' => 'Pending',
                'delivery_notes' => $order->notes,
            ]);

            // Update customer balance if unpaid
            if ($order->payment_status === 'Unpaid') {
                $customer->increment('outstanding_balance', $totalAmount);
            }

            ActivityLog::create([
                'user_id' => $userId,
                'action' => 'order_created',
                'description' => "Order {$orderNumber} created manually for {$customer->displayName}",
                'subject_type' => Order::class,
                'subject_id' => $order->id,
            ]);

            return $order;
        });
    }

    /**
     * Repeat Order from previous order
     */
    public function repeatOrder(Order $previousOrder, ?array $overrideQuantities = null, ?int $userId = null): Order
    {
        return DB::transaction(function () use ($previousOrder, $overrideQuantities, $userId) {
            $customer = $previousOrder->customer;
            $orderNumber = $this->generateOrderNumber();

            $newOrder = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'conversation_id' => $previousOrder->conversation_id,
                'whatsapp_account_id' => $previousOrder->whatsapp_account_id,
                'source' => 'Repeat Order',
                'status' => 'New',
                'subtotal' => 0,
                'discount' => 0,
                'delivery_charge' => 0,
                'total_amount' => 0,
                'payment_status' => 'Unpaid',
                'payment_method' => $previousOrder->payment_method ?: 'Cash on Delivery',
                'delivery_address' => $previousOrder->delivery_address ?: $customer->address,
                'delivery_date' => now()->addDay()->toDateString(),
                'time_slot' => $previousOrder->time_slot ?: 'Morning (6:00 AM - 9:00 AM)',
                'notes' => "Repeated from order {$previousOrder->order_number}",
                'created_by' => $userId,
            ]);

            $subtotal = 0;

            foreach ($previousOrder->orderItems as $item) {
                $qty = (float)$item->quantity;
                if ($overrideQuantities && isset($overrideQuantities[$item->product_id])) {
                    $qty = (float)$overrideQuantities[$item->product_id];
                }

                $product = $item->product_id ? Product::find($item->product_id) : null;
                $unitPrice = $product ? $customer->getProductPrice($product) : (float)$item->unit_price;
                $lineSubtotal = round($qty * $unitPrice, 2);
                $subtotal += $lineSubtotal;

                OrderItem::create([
                    'order_id' => $newOrder->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'unit' => $item->unit,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineSubtotal,
                ]);

                if ($product) {
                    $product->increment('reserved_quantity', $qty);
                }
            }

            $totalAmount = $subtotal;
            $newOrder->update([
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
            ]);

            Delivery::create([
                'order_id' => $newOrder->id,
                'driver_name' => 'Rashid Khan',
                'driver_phone' => '+971 50 882 1940',
                'vehicle_number' => 'DXB-VAN-4028',
                'delivery_date' => $newOrder->delivery_date,
                'time_slot' => $newOrder->time_slot,
                'status' => 'Pending',
            ]);

            $customer->increment('outstanding_balance', $totalAmount);

            ActivityLog::create([
                'user_id' => $userId,
                'action' => 'repeat_order_created',
                'description' => "Order {$orderNumber} repeated from {$previousOrder->order_number} for {$customer->displayName}",
                'subject_type' => Order::class,
                'subject_id' => $newOrder->id,
            ]);

            return $newOrder;
        });
    }

    /**
     * Update order status with inventory deduction upon fulfillment
     */
    public function updateStatus(Order $order, string $newStatus, ?int $userId = null): Order
    {
        $oldStatus = $order->status;
        $order->update(['status' => $newStatus]);

        // If status changed to Delivered:
        if ($newStatus === 'Delivered' && $oldStatus !== 'Delivered') {
            // Deduct actual physical stock and release reserved quantity
            foreach ($order->orderItems as $item) {
                if ($item->product_id) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->decrement('reserved_quantity', min((float)$product->reserved_quantity, (float)$item->quantity));
                        $product->decrement('stock_quantity', (float)$item->quantity);

                        InventoryTransaction::create([
                            'product_id' => $product->id,
                            'type' => 'sale',
                            'quantity' => -(float)$item->quantity,
                            'balance_after' => (float)$product->stock_quantity,
                            'reference_type' => 'Order',
                            'reference_id' => $order->id,
                            'notes' => "Dispatched for order {$order->order_number}",
                            'created_by' => $userId,
                        ]);
                    }
                }
            }

            if ($order->delivery) {
                $order->delivery->update([
                    'status' => 'Delivered',
                    'delivered_at' => now(),
                ]);
            }
        } elseif ($newStatus === 'Cancelled' && $oldStatus !== 'Cancelled') {
            // Release reserved stock
            foreach ($order->orderItems as $item) {
                if ($item->product_id) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->decrement('reserved_quantity', min((float)$product->reserved_quantity, (float)$item->quantity));
                    }
                }
            }

            if ($order->payment_status === 'Unpaid') {
                $order->customer->decrement('outstanding_balance', min((float)$order->customer->outstanding_balance, (float)$order->total_amount));
            }
        } elseif ($newStatus === 'Out for Delivery') {
            if ($order->delivery) {
                $order->delivery->update(['status' => 'Out for Delivery']);
            }
        }

        ActivityLog::create([
            'user_id' => $userId,
            'action' => 'order_status_updated',
            'description' => "Order {$order->order_number} status changed from {$oldStatus} to {$newStatus}",
            'subject_type' => Order::class,
            'subject_id' => $order->id,
        ]);

        return $order;
    }
}
