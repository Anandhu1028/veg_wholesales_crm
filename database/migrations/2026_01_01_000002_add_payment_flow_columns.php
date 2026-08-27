<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'credit_enabled')) {
                $table->boolean('credit_enabled')->default(true)->after('credit_limit');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'paid_amount')) {
                $table->decimal('paid_amount', 12, 2)->default(0.00)->after('total_amount');
            }
            if (!Schema::hasColumn('orders', 'pending_amount')) {
                $table->decimal('pending_amount', 12, 2)->default(0.00)->after('paid_amount');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_date');
            }
            if (!Schema::hasColumn('payments', 'payment_status')) {
                $table->string('payment_status')->default('Paid')->after('amount');
            }
            if (!Schema::hasColumn('payments', 'reference')) {
                $table->string('reference')->nullable()->after('payment_method');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['paid_at', 'payment_status', 'reference']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'pending_amount']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['credit_enabled']);
        });
    }
};
