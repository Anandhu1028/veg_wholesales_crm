<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Order;
use App\Models\Customer;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Record a payment from the Payments page or via API.
     * Handles partial payments and full settlements.
     */
    public function recordPayment(array $data, ?int $userId = null): Payment
    {
        return DB::transaction(function () use ($data, $userId) {
            $customer = Customer::findOrFail($data['customer_id']);
            $order    = !empty($data['order_id']) ? Order::find($data['order_id']) : null;
            $amount   = (float) $data['amount'];

            $payment = $this->createPaymentRecord([
                'customer_id'      => $customer->id,
                'order_id'         => $order?->id,
                'amount'           => $amount,
                'payment_method'   => $data['payment_method'] ?? 'Cash',
                'reference_number' => $data['reference_number'] ?? null,
                'reference'        => $data['reference_number'] ?? null,
                'payment_date'     => $data['payment_date'] ?? now()->toDateString(),
                'notes'            => $data['notes'] ?? null,
                'payment_status'   => 'Paid',
                'paid_at'          => now(),
                'received_by'      => $userId,
            ]);

            // Reduce customer outstanding balance (floor at 0)
            $deductAmt = min((float) $customer->outstanding_balance, $amount);
            if ($deductAmt > 0) {
                $customer->decrement('outstanding_balance', $deductAmt);
            }

            // If attached to an order, recalculate order payment status
            if ($order) {
                $order->recalculatePaymentStatus();
            }

            ActivityLog::create([
                'user_id'      => $userId,
                'action'       => 'payment_recorded',
                'description'  => "Payment {$payment->payment_number} of ₹" . number_format($amount, 2) . " received from {$customer->displayName}",
                'subject_type' => Payment::class,
                'subject_id'   => $payment->id,
            ]);

            return $payment;
        });
    }

    /**
     * Record payment directly linked to a specific order (used from Order detail page).
     * Supports partial payments — can be called multiple times.
     */
    public function recordPaymentForOrder(Order $order, array $data, ?int $userId = null): Payment
    {
        return DB::transaction(function () use ($order, $data, $userId) {
            $customer = $order->customer;
            $amount   = (float) $data['amount'];

            $payment = $this->createPaymentRecord([
                'customer_id'      => $customer->id,
                'order_id'         => $order->id,
                'amount'           => $amount,
                'payment_method'   => $data['payment_method'] ?? 'Cash',
                'reference_number' => $data['reference_number'] ?? null,
                'reference'        => $data['reference_number'] ?? null,
                'payment_date'     => $data['payment_date'] ?? now()->toDateString(),
                'notes'            => $data['notes'] ?? null,
                'payment_status'   => 'Paid',
                'paid_at'          => now(),
                'received_by'      => $userId,
            ]);

            // Reduce customer outstanding balance
            $deductAmt = min((float) $customer->outstanding_balance, $amount);
            if ($deductAmt > 0) {
                $customer->decrement('outstanding_balance', $deductAmt);
            }

            // Recalculate order paid/pending and status
            $order->recalculatePaymentStatus();

            ActivityLog::create([
                'user_id'      => $userId,
                'action'       => 'order_payment_recorded',
                'description'  => "Payment {$payment->payment_number} of ₹" . number_format($amount, 2) . " applied to order #{$order->order_number}",
                'subject_type' => Order::class,
                'subject_id'   => $order->id,
            ]);

            return $payment;
        });
    }

    /**
     * Generate payment number and create the payment row.
     */
    protected function createPaymentRecord(array $attributes): Payment
    {
        $latestPay = Payment::latest('id')->first();
        $payNum    = 'PAY-' . str_pad(($latestPay ? $latestPay->id + 4001 : 4001), 4, '0', STR_PAD_LEFT);

        return Payment::create(array_merge($attributes, [
            'payment_number' => $payNum,
            'status'         => 'Completed',
        ]));
    }
}
