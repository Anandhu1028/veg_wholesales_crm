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
        // Update users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin')->after('email'); // admin, order_staff, accounts, delivery_staff
            $table->string('phone')->nullable()->after('role');
            $table->string('status')->default('active')->after('phone');
            $table->string('avatar')->nullable()->after('status');
        });

        // WhatsApp Accounts
        Schema::create('whatsapp_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. WA 1
            $table->string('phone_number')->unique(); // e.g. +971 55 125 4003
            $table->string('provider')->default('demo'); // demo, meta_cloud
            $table->string('status')->default('connected'); // connected, disconnected, pairing
            $table->string('mode')->default('simulated'); // simulated, live
            $table->string('webhook_url')->nullable();
            $table->string('api_key')->nullable();
            $table->string('phone_number_id')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        // Customers
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('business_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp_number')->unique();
            $table->string('email')->nullable();
            $table->string('business_type')->default('Wholesale'); // Wholesale, Hotel, Restaurant, Supermarket, Retailer
            $table->text('address')->nullable();
            $table->string('city')->default('Dubai');
            $table->decimal('credit_limit', 12, 2)->default(50000.00);
            $table->decimal('outstanding_balance', 12, 2)->default(0.00);
            $table->string('status')->default('active'); // active, inactive, blocked
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Customer Addresses
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('title')->default('Primary Location');
            $table->text('address_line');
            $table->string('city')->default('Dubai');
            $table->string('area')->nullable();
            $table->string('pincode')->nullable();
            $table->boolean('is_default')->default(true);
            $table->timestamps();
        });

        // Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('category')->default('Vegetables'); // Vegetables, Leafy Greens, Exotic, Herbs
            $table->string('unit')->default('kg'); // kg, box, crate, piece, bag
            $table->decimal('base_price', 10, 2); // Default selling price
            $table->decimal('cost_price', 10, 2)->default(0.00);
            $table->decimal('stock_quantity', 10, 2)->default(0.00);
            $table->decimal('reserved_quantity', 10, 2)->default(0.00);
            $table->decimal('low_stock_threshold', 10, 2)->default(50.00);
            $table->string('status')->default('active'); // active, inactive, out_of_stock
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Customer Specific Product Pricing
        Schema::create('customer_product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('custom_price', 10, 2);
            $table->timestamps();

            $table->unique(['customer_id', 'product_id']);
        });

        // Conversations
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('whatsapp_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Assigned staff
            $table->string('status')->default('bot_active'); // bot_active, human_required, closed
            $table->string('bot_state')->default('START'); // START, WELCOME, ORDER_SELECTION, COLLECT_ORDER, CONFIRM_ORDER, ORDER_CREATED, HUMAN_HANDOFF, COMPLETED
            $table->json('bot_context')->nullable(); // holds draft order items, step data, etc.
            $table->string('last_message')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->integer('unread_count')->default(0);
            $table->boolean('is_starred')->default(false);
            $table->timestamps();
        });

        // Messages
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->enum('sender_type', ['customer', 'bot', 'staff'])->default('customer');
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->string('message_type')->default('text'); // text, quick_reply, order_summary, interactive, template
            $table->json('metadata')->nullable();
            $table->boolean('is_read')->default(false);
            $table->string('external_id')->nullable();
            $table->timestamps();
        });

        // Orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // e.g. ORD-1256
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('whatsapp_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source')->default('WhatsApp'); // WhatsApp, Manual, Repeat Order
            $table->string('status')->default('New'); // New, Confirmed, Processing, Ready, Out for Delivery, Delivered, Cancelled
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('discount', 12, 2)->default(0.00);
            $table->decimal('delivery_charge', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->string('payment_status')->default('Unpaid'); // Unpaid, Partially Paid, Paid, Overdue
            $table->string('payment_method')->default('Cash on Delivery'); // Cash, Bank Transfer, UPI, Cheque, Credit
            $table->text('delivery_address')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('time_slot')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Order Items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('unit')->default('kg');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        // Inventory Transactions
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // purchase, sale, wastage, damage, adjustment, return
            $table->decimal('quantity', 10, 2); // positive for in, negative for out
            $table->decimal('balance_after', 10, 2);
            $table->string('reference_type')->nullable(); // Order, PurchaseOrder, ManualAdjustment
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Suppliers
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name');
            $table->string('phone');
            $table->string('whatsapp_number')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('payment_terms')->default('Net 30'); // Net 15, Net 30, Cash on Delivery, Advance
            $table->decimal('outstanding_balance', 12, 2)->default(0.00);
            $table->string('status')->default('active'); // active, inactive
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Purchase Orders
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique(); // e.g. PO-8021
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('received_date')->nullable();
            $table->string('status')->default('Draft'); // Draft, Ordered, Received, Cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Purchase Order Items
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('unit')->default('kg');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        // Deliveries
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('time_slot')->nullable(); // Morning (6 AM - 9 AM), Afternoon, Evening
            $table->string('status')->default('Pending'); // Pending, Preparing, Ready, Out for Delivery, Delivered, Failed
            $table->text('delivery_notes')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        // Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique(); // e.g. PAY-4091
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('Cash'); // Cash, Bank Transfer, UPI, Cheque
            $table->string('reference_number')->nullable();
            $table->date('payment_date');
            $table->string('status')->default('Completed'); // Completed, Pending, Failed
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // Fuel, Vehicle Maintenance, Labor, Packaging, Electricity, Rent, Other
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->string('payment_method')->default('Cash');
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Activity Logs
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // order_created, status_updated, whatsapp_simulated, etc.
            $table->text('description');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('customer_product_prices');
        Schema::dropIfExists('products');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('whatsapp_accounts');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'status', 'avatar']);
        });
    }
};
