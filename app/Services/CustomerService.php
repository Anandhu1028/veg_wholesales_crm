<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerProductPrice;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function getCustomers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Customer::withCount(['orders', 'conversations'])
            ->with(['latestOrder'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('business_name', 'like', $search)
                  ->orWhere('phone', 'like', $search)
                  ->orWhere('whatsapp_number', 'like', $search)
                  ->orWhere('email', 'like', $search);
            });
        }

        if (!empty($filters['business_type'])) {
            $query->where('business_type', $filters['business_type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function getCustomerStats(Customer $customer): array
    {
        $ordersCount = $customer->orders()->count();
        $totalSpent = (float)$customer->orders()->where('status', '!=', 'Cancelled')->sum('total_amount');
        $outstanding = (float)$customer->outstanding_balance;
        $avgOrderValue = $ordersCount > 0 ? round($totalSpent / $ordersCount, 2) : 0.00;

        return [
            'total_orders' => $ordersCount,
            'total_spent' => $totalSpent,
            'outstanding_balance' => $outstanding,
            'avg_order_value' => $avgOrderValue,
        ];
    }

    public function setCustomPrice(Customer $customer, int $productId, float $customPrice): CustomerProductPrice
    {
        return CustomerProductPrice::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'product_id' => $productId,
            ],
            [
                'custom_price' => $customPrice,
            ]
        );
    }
}
