<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Conversation;
use App\Models\Product;
use App\Models\WhatsAppAccount;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\Payment;
use App\Models\Delivery;

class FreshDealWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!User::where('email', 'admin@freshdeal.com')->exists()) {
            $this->artisan('db:seed');
        }
    }

    public function test_login_and_dashboard(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('FreshDeal');

        $loginResponse = $this->post('/login', [
            'email' => 'admin@freshdeal.com',
            'password' => 'password',
        ]);
        $loginResponse->assertRedirect('/dashboard');

        $admin = User::where('email', 'admin@freshdeal.com')->first();
        $dashResponse = $this->actingAs($admin)->get('/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Dashboard');
        $dashResponse->assertSee('Connected Numbers');
    }

    public function test_inbox_rendering(): void
    {
        $admin = User::where('email', 'admin@freshdeal.com')->first();
        $response = $this->actingAs($admin)->get('/inbox');
        $response->assertStatus(200);
        $response->assertSee('Common Inbox');
        $response->assertSee('Simulate');
    }

    /**
     * TEST 1: NEW CUSTOMER ONBOARDING FLOW
     */
    public function test_new_customer_onboarding_flow(): void
    {
        $admin = User::where('email', 'admin@freshdeal.com')->first();
        $wa = WhatsAppAccount::first();

        // Create a new customer without business_type or address
        $newPhone = '+971 50 ' . rand(200, 899) . ' ' . rand(1000, 9999);
        $customer = Customer::create([
            'name' => 'Emirates Catering Co.',
            'business_name' => 'Emirates Catering',
            'phone' => $newPhone,
            'whatsapp_number' => $newPhone,
            'business_type' => null,
            'address' => null,
            'city' => 'Dubai',
            'status' => 'active',
        ]);

        // Step 1: Customer sends "Hi"
        $this->actingAs($admin)->post('/inbox/simulate', [
            'customer_id' => $customer->id,
            'whatsapp_account_id' => $wa->id,
            'message' => 'Hi',
        ]);

        $conv = Conversation::where('customer_id', $customer->id)->first();
        $this->assertEquals('COLLECT_BUSINESS_TYPE', $conv->bot_state);
        $this->assertStringContainsString('Business Type', $conv->last_message);

        // Step 2: Customer responds with "Wholesale"
        $this->actingAs($admin)->post('/inbox/simulate', [
            'customer_id' => $customer->id,
            'whatsapp_account_id' => $wa->id,
            'message' => 'Wholesale',
        ]);

        $customer->refresh();
        $this->assertEquals('Wholesale', $customer->business_type);

        $conv->refresh();
        $this->assertEquals('COLLECT_ADDRESS', $conv->bot_state);
        $this->assertStringContainsString('delivery address', $conv->last_message);

        // Step 3: Customer responds with Address
        $this->actingAs($admin)->post('/inbox/simulate', [
            'customer_id' => $customer->id,
            'whatsapp_account_id' => $wa->id,
            'message' => 'Shop 42, Block C, Dubai, UAE',
        ]);

        $customer->refresh();
        $this->assertEquals('Shop 42, Block C, Dubai, UAE', $customer->address);

        $conv->refresh();
        $this->assertEquals('WELCOME', $conv->bot_state);
        $this->assertStringContainsString('saved', $conv->last_message);
    }

    /**
     * TEST 2: EXISTING CUSTOMER SKIPS ONBOARDING
     */
    public function test_existing_customer_skips_onboarding(): void
    {
        $admin = User::where('email', 'admin@freshdeal.com')->first();
        $customer = Customer::where('business_name', 'ABC Traders')->first();
        $wa = WhatsAppAccount::first();

        // Customer has business_type and address filled
        $this->assertNotEmpty($customer->business_type);
        $this->assertNotEmpty($customer->address);

        $this->actingAs($admin)->post('/inbox/simulate', [
            'customer_id' => $customer->id,
            'whatsapp_account_id' => $wa->id,
            'message' => 'Hi',
        ]);

        $conv = Conversation::where('customer_id', $customer->id)->first();
        $this->assertEquals('WELCOME', $conv->bot_state);
        $this->assertStringContainsString('Welcome back', $conv->last_message);
    }

    /**
     * TEST 3: INCOMPLETE PROFILE (Missing Address Only)
     */
    public function test_incomplete_profile_asks_only_missing_field(): void
    {
        $admin = User::where('email', 'admin@freshdeal.com')->first();
        $wa = WhatsAppAccount::first();

        $incompletePhone = '+971 52 ' . rand(200, 899) . ' ' . rand(1000, 9999);
        $customer = Customer::create([
            'name' => 'Royal Bistro',
            'business_name' => 'Royal Bistro',
            'phone' => $incompletePhone,
            'whatsapp_number' => $incompletePhone,
            'business_type' => 'Hotel / Restaurant',
            'address' => null, // Missing address
            'city' => 'Dubai',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->post('/inbox/simulate', [
            'customer_id' => $customer->id,
            'whatsapp_account_id' => $wa->id,
            'message' => 'Hi',
        ]);

        $conv = Conversation::where('customer_id', $customer->id)->first();
        $this->assertEquals('COLLECT_ADDRESS', $conv->bot_state);
        $this->assertStringContainsString('delivery address', $conv->last_message);
    }

    /**
     * TEST 4: ORDER PERSISTS CUSTOMER DEFAULT DELIVERY ADDRESS
     */
    public function test_order_stores_customer_delivery_address(): void
    {
        $admin = User::where('email', 'admin@freshdeal.com')->first();
        $customer = Customer::where('business_name', 'ABC Traders')->first();
        $wa = WhatsAppAccount::first();

        // Customer sends vegetable items
        $this->actingAs($admin)->post('/inbox/simulate', [
            'customer_id' => $customer->id,
            'whatsapp_account_id' => $wa->id,
            'message' => "Tomato 10kg\nOnion 10kg",
        ]);

        // Choose COD
        $this->actingAs($admin)->post('/inbox/simulate', [
            'customer_id' => $customer->id,
            'whatsapp_account_id' => $wa->id,
            'message' => 'Cash on Delivery',
        ]);

        // Confirm
        $this->actingAs($admin)->post('/inbox/simulate', [
            'customer_id' => $customer->id,
            'whatsapp_account_id' => $wa->id,
            'message' => 'Confirm COD Order',
        ]);

        $order = Order::where('customer_id', $customer->id)->latest('id')->first();
        $this->assertNotNull($order->delivery_address);
        $this->assertEquals($customer->address, $order->delivery_address);
    }

    /**
     * TEST 5: STAFF EDITS CUSTOMER DETAILS VIA INBOX MODAL
     */
    public function test_staff_edits_customer_details(): void
    {
        $admin = User::where('email', 'admin@freshdeal.com')->first();
        $customer = Customer::where('business_name', 'ABC Traders')->first();

        $updateResponse = $this->actingAs($admin)->put("/customers/{$customer->id}", [
            'name' => 'Anas Trading Updated',
            'business_name' => 'ABC Traders LLC',
            'whatsapp_number' => $customer->whatsapp_number,
            'business_type' => 'Supermarket',
            'address' => 'Warehouse 15, Al Aweer Produce Market, Dubai',
            'email' => 'sales@abctraders.ae',
        ]);
        $updateResponse->assertRedirect();

        $customer->refresh();
        $this->assertEquals('Supermarket', $customer->business_type);
        $this->assertEquals('Warehouse 15, Al Aweer Produce Market, Dubai', $customer->address);
    }

    public function test_full_bot_order_flow_simulation_with_payment(): void
    {
        $admin = User::where('email', 'admin@freshdeal.com')->first();
        $customer = Customer::where('business_name', 'ABC Traders')->first();
        $wa = WhatsAppAccount::first();

        // 1. Customer sends vegetable order
        $simResponse2 = $this->actingAs($admin)->post('/inbox/simulate', [
            'customer_id' => $customer->id,
            'whatsapp_account_id' => $wa->id,
            'message' => "Tomato 20kg\nOnion 30kg\nPotato 50kg",
        ]);
        $simResponse2->assertRedirect();

        $conv = Conversation::where('customer_id', $customer->id)->first();
        $this->assertEquals('SELECT_PAYMENT_METHOD', $conv->bot_state);
        $this->assertNotEmpty($conv->bot_context['items']);

        // 2. Customer selects 'Pay Now'
        $simResponse3 = $this->actingAs($admin)->post('/inbox/simulate', [
            'customer_id' => $customer->id,
            'whatsapp_account_id' => $wa->id,
            'message' => 'Pay Now',
        ]);
        $simResponse3->assertRedirect();

        $conv->refresh();
        $this->assertEquals('PAYMENT_PENDING_SIMULATION', $conv->bot_state);

        // 3. Simulate Successful Payment
        $initialOrderCount = Order::where('customer_id', $customer->id)->count();

        $simResponse4 = $this->actingAs($admin)->post('/inbox/simulate', [
            'customer_id' => $customer->id,
            'whatsapp_account_id' => $wa->id,
            'message' => 'Simulate Successful Payment',
        ]);
        $simResponse4->assertRedirect();

        $conv->refresh();
        $this->assertEquals('ORDER_CREATED', $conv->bot_state);
        $this->assertEquals($initialOrderCount + 1, Order::where('customer_id', $customer->id)->count());

        $latestOrder = Order::where('customer_id', $customer->id)->latest('id')->first();
        $this->assertEquals('Confirmed', $latestOrder->status);
        $this->assertEquals('Paid', $latestOrder->payment_status);
        $this->assertEquals('UPI', $latestOrder->payment_method);
        $this->assertGreaterThan(0, $latestOrder->total_amount);

        // Verify payment row in DB
        $payment = Payment::where('order_id', $latestOrder->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('Paid', $payment->payment_status);
    }
}
