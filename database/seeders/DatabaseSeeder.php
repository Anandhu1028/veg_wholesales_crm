<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\WhatsAppAccount;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Product;
use App\Models\CustomerProductPrice;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Delivery;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\InventoryTransaction;
use App\Models\ActivityLog;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@freshdeal.com'],
            [
                'name' => 'Anandhu',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+971 55 125 4003',
                'status' => 'active',
            ]
        );

        $orderStaff = User::firstOrCreate(
            ['email' => 'orders@freshdeal.com'],
            [
                'name' => 'Rahul Sharma',
                'password' => Hash::make('password'),
                'role' => 'order_staff',
                'phone' => '+971 52 771 9021',
                'status' => 'active',
            ]
        );

        $accountsUser = User::firstOrCreate(
            ['email' => 'accounts@freshdeal.com'],
            [
                'name' => 'Priya Nair',
                'password' => Hash::make('password'),
                'role' => 'accounts',
                'phone' => '+971 56 443 1189',
                'status' => 'active',
            ]
        );

        $deliveryUser = User::firstOrCreate(
            ['email' => 'delivery@freshdeal.com'],
            [
                'name' => 'Rashid Khan',
                'password' => Hash::make('password'),
                'role' => 'delivery_staff',
                'phone' => '+971 50 882 1940',
                'status' => 'active',
            ]
        );

        // 2. WhatsApp Accounts
        $wa1 = WhatsAppAccount::firstOrCreate(
            ['phone_number' => '+971 55 125 4003'],
            [
                'name' => 'WA 1',
                'provider' => 'demo',
                'status' => 'connected',
                'mode' => 'simulated',
                'settings' => ['auto_reply' => true, 'operating_hours' => '24/7'],
            ]
        );

        WhatsAppAccount::firstOrCreate(
            ['phone_number' => '+971 55 982 1102'],
            [
                'name' => 'WA 2',
                'provider' => 'demo',
                'status' => 'connected',
                'mode' => 'simulated',
                'settings' => ['auto_reply' => true],
            ]
        );

        // 3. Products
        $productsData = [
            ['name' => 'Tomato', 'code' => 'VEG-TOM-01', 'category' => 'Vegetables', 'unit' => 'kg', 'base_price' => 40.00, 'cost_price' => 28.00, 'stock_quantity' => 1250.00, 'low_stock_threshold' => 200.00],
            ['name' => 'Onion', 'code' => 'VEG-ONI-02', 'category' => 'Vegetables', 'unit' => 'kg', 'base_price' => 30.00, 'cost_price' => 20.00, 'stock_quantity' => 1800.00, 'low_stock_threshold' => 300.00],
            ['name' => 'Potato', 'code' => 'VEG-POT-03', 'category' => 'Vegetables', 'unit' => 'kg', 'base_price' => 25.00, 'cost_price' => 16.00, 'stock_quantity' => 2400.00, 'low_stock_threshold' => 400.00],
            ['name' => 'Carrot', 'code' => 'VEG-CAR-04', 'category' => 'Vegetables', 'unit' => 'kg', 'base_price' => 35.00, 'cost_price' => 22.00, 'stock_quantity' => 850.00, 'low_stock_threshold' => 150.00],
            ['name' => 'Beans', 'code' => 'VEG-BEA-05', 'category' => 'Vegetables', 'unit' => 'kg', 'base_price' => 60.00, 'cost_price' => 42.00, 'stock_quantity' => 420.00, 'low_stock_threshold' => 100.00],
            ['name' => 'Cabbage', 'code' => 'VEG-CAB-06', 'category' => 'Vegetables', 'unit' => 'kg', 'base_price' => 28.00, 'cost_price' => 18.00, 'stock_quantity' => 620.00, 'low_stock_threshold' => 120.00],
            ['name' => 'Cauliflower', 'code' => 'VEG-CAU-07', 'category' => 'Vegetables', 'unit' => 'kg', 'base_price' => 45.00, 'cost_price' => 30.00, 'stock_quantity' => 540.00, 'low_stock_threshold' => 100.00],
            ['name' => 'Cucumber', 'code' => 'VEG-CUC-08', 'category' => 'Vegetables', 'unit' => 'kg', 'base_price' => 32.00, 'cost_price' => 20.00, 'stock_quantity' => 710.00, 'low_stock_threshold' => 150.00],
            ['name' => 'Green Chilli', 'code' => 'VEG-GCH-09', 'category' => 'Vegetables', 'unit' => 'kg', 'base_price' => 75.00, 'cost_price' => 50.00, 'stock_quantity' => 310.00, 'low_stock_threshold' => 80.00],
            ['name' => 'Ginger', 'code' => 'VEG-GIN-10', 'category' => 'Vegetables', 'unit' => 'kg', 'base_price' => 120.00, 'cost_price' => 85.00, 'stock_quantity' => 260.00, 'low_stock_threshold' => 60.00],
            ['name' => 'Garlic', 'code' => 'VEG-GAR-11', 'category' => 'Vegetables', 'unit' => 'kg', 'base_price' => 140.00, 'cost_price' => 95.00, 'stock_quantity' => 340.00, 'low_stock_threshold' => 70.00],
            ['name' => 'Capsicum', 'code' => 'VEG-CAP-12', 'category' => 'Vegetables', 'unit' => 'kg', 'base_price' => 65.00, 'cost_price' => 44.00, 'stock_quantity' => 390.00, 'low_stock_threshold' => 90.00],
        ];

        $productModels = [];
        foreach ($productsData as $p) {
            $prod = Product::firstOrCreate(['code' => $p['code']], $p);
            $productModels[$prod->name] = $prod;
        }

        // 4. Customers
        $customersData = [
            [
                'name' => 'Anas Trading',
                'business_name' => 'ABC Traders',
                'phone' => '+971 50 112 3456',
                'whatsapp_number' => '+971 50 112 3456',
                'email' => 'orders@abctraders.ae',
                'business_type' => 'Wholesale',
                'address' => 'Shop 42, Block C, Dubai Central Fruit & Veg Market, Ras Al Khor',
                'city' => 'Dubai',
                'credit_limit' => 100000.00,
                'outstanding_balance' => 42500.00,
                'status' => 'active',
                'notes' => 'Key wholesale buyer. Preferred daily delivery before 7:00 AM.',
            ],
            [
                'name' => 'Sultan Ahmed',
                'business_name' => 'Fresh Mart',
                'phone' => '+971 55 223 4567',
                'whatsapp_number' => '+971 55 223 4567',
                'email' => 'purchase@freshmart.ae',
                'business_type' => 'Supermarket',
                'address' => 'Al Rigga Street, Deira',
                'city' => 'Dubai',
                'credit_limit' => 60000.00,
                'outstanding_balance' => 18400.00,
                'status' => 'active',
            ],
            [
                'name' => 'Chef Vikram',
                'business_name' => 'Green Leaf Hotel',
                'phone' => '+971 52 334 5678',
                'whatsapp_number' => '+971 52 334 5678',
                'email' => 'kitchen@greenleafhotel.com',
                'business_type' => 'Hotel',
                'address' => 'Mankhool Road, Bur Dubai',
                'city' => 'Dubai',
                'credit_limit' => 75000.00,
                'outstanding_balance' => 24000.00,
                'status' => 'active',
            ],
            [
                'name' => 'Sree Kumar',
                'business_name' => 'Sree Stores',
                'phone' => '+971 54 445 6789',
                'whatsapp_number' => '+971 54 445 6789',
                'email' => 'sreestores@gmail.com',
                'business_type' => 'Retailer',
                'address' => '18th Street, Al Karama',
                'city' => 'Dubai',
                'credit_limit' => 30000.00,
                'outstanding_balance' => 8200.00,
                'status' => 'active',
            ],
            [
                'name' => 'Tariq Mansoor',
                'business_name' => 'Natural Foods',
                'phone' => '+971 50 556 7890',
                'whatsapp_number' => '+971 50 556 7890',
                'email' => 'info@naturalfoods.ae',
                'business_type' => 'Wholesale',
                'address' => 'Warehouse 14, Al Quoz Industrial Area 3',
                'city' => 'Dubai',
                'credit_limit' => 80000.00,
                'outstanding_balance' => 15000.00,
                'status' => 'active',
            ],
            [
                'name' => 'Farhan Qureshi',
                'business_name' => 'Best Supply Co.',
                'phone' => '+971 56 667 8901',
                'whatsapp_number' => '+971 56 667 8901',
                'email' => 'supply@bestsupply.com',
                'business_type' => 'Wholesale',
                'address' => 'Ras Al Khor Industrial 2',
                'city' => 'Dubai',
                'credit_limit' => 90000.00,
                'outstanding_balance' => 31200.00,
                'status' => 'active',
            ],
            [
                'name' => 'Naveen George',
                'business_name' => 'Village Basket',
                'phone' => '+971 55 778 9012',
                'whatsapp_number' => '+971 55 778 9012',
                'email' => 'procurement@villagebasket.ae',
                'business_type' => 'Supermarket',
                'address' => 'Al Wasl Road, Jumeirah 2',
                'city' => 'Dubai',
                'credit_limit' => 50000.00,
                'outstanding_balance' => 9500.00,
                'status' => 'active',
            ],
            [
                'name' => 'Manager Kareem',
                'business_name' => 'Hotel Blue Moon',
                'phone' => '+971 52 889 0123',
                'whatsapp_number' => '+971 52 889 0123',
                'email' => 'fnb@hotelbluemoon.ae',
                'business_type' => 'Hotel',
                'address' => 'Downtown Boulevard, Downtown',
                'city' => 'Dubai',
                'credit_limit' => 85000.00,
                'outstanding_balance' => 12400.00,
                'status' => 'active',
            ],
        ];

        $customerModels = [];
        foreach ($customersData as $c) {
            $cust = Customer::firstOrCreate(['whatsapp_number' => $c['whatsapp_number']], $c);
            $customerModels[$cust->business_name] = $cust;

            // Address
            CustomerAddress::firstOrCreate(
                ['customer_id' => $cust->id, 'title' => 'Primary Delivery Location'],
                [
                    'address_line' => $cust->address,
                    'city' => $cust->city,
                    'is_default' => true,
                ]
            );
        }

        // 5. Customer Specific Pricing for ABC Traders
        $abc = $customerModels['ABC Traders'];
        if ($abc) {
            CustomerProductPrice::updateOrCreate(
                ['customer_id' => $abc->id, 'product_id' => $productModels['Tomato']->id],
                ['custom_price' => 37.00]
            );
            CustomerProductPrice::updateOrCreate(
                ['customer_id' => $abc->id, 'product_id' => $productModels['Onion']->id],
                ['custom_price' => 28.00]
            );
            CustomerProductPrice::updateOrCreate(
                ['customer_id' => $abc->id, 'product_id' => $productModels['Potato']->id],
                ['custom_price' => 23.00]
            );
        }

        // 6. Suppliers
        $sup1 = Supplier::firstOrCreate(
            ['company_name' => 'Green Valley Agro Farms'],
            [
                'name' => 'Haris Al Mazrouei',
                'phone' => '+971 50 991 2233',
                'whatsapp_number' => '+971 50 991 2233',
                'email' => 'sales@greenvalleyfarms.ae',
                'address' => 'Agricultural Zone, Al Ain',
                'payment_terms' => 'Net 30',
                'outstanding_balance' => 35000.00,
                'status' => 'active',
            ]
        );

        $sup2 = Supplier::firstOrCreate(
            ['company_name' => 'Oasis Fresh Agri Co.'],
            [
                'name' => 'Abdullah Al Nuaimi',
                'phone' => '+971 55 882 3344',
                'whatsapp_number' => '+971 55 882 3344',
                'email' => 'orders@oasisagri.ae',
                'address' => 'Khatt Agricultural Area, Ras Al Khaimah',
                'payment_terms' => 'Net 15',
                'outstanding_balance' => 22000.00,
                'status' => 'active',
            ]
        );

        // 7. Purchase Orders
        $po1 = PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-8021'],
            [
                'supplier_id' => $sup1->id,
                'total_amount' => 45000.00,
                'order_date' => now()->subDays(3)->toDateString(),
                'expected_delivery_date' => now()->subDay()->toDateString(),
                'received_date' => now()->subDay()->toDateString(),
                'status' => 'Received',
                'notes' => 'Fresh harvest tomatoes and onions received in excellent condition.',
                'created_by' => $admin->id,
            ]
        );

        PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po1->id, 'product_id' => $productModels['Tomato']->id],
            [
                'product_name' => 'Tomato',
                'unit' => 'kg',
                'quantity' => 1000.00,
                'unit_price' => 25.00,
                'subtotal' => 25000.00,
            ]
        );

        PurchaseOrderItem::firstOrCreate(
            ['purchase_order_id' => $po1->id, 'product_id' => $productModels['Onion']->id],
            [
                'product_name' => 'Onion',
                'unit' => 'kg',
                'quantity' => 1000.00,
                'unit_price' => 20.00,
                'subtotal' => 20000.00,
            ]
        );

        // 8. Orders for ABC Traders & Other Customers to establish rich history
        $ordersSeed = [
            [
                'customer' => 'ABC Traders',
                'order_number' => 'ORD-1256',
                'source' => 'WhatsApp',
                'status' => 'New',
                'subtotal' => 2950.00,
                'total' => 2950.00,
                'payment_status' => 'Unpaid',
                'created_at' => now()->subMinutes(15),
                'items' => [
                    ['product' => 'Tomato', 'qty' => 20, 'price' => 37.00, 'sub' => 740.00],
                    ['product' => 'Onion', 'qty' => 30, 'price' => 28.00, 'sub' => 840.00],
                    ['product' => 'Potato', 'qty' => 60, 'price' => 23.00, 'sub' => 1370.00],
                ]
            ],
            [
                'customer' => 'ABC Traders',
                'order_number' => 'ORD-1255',
                'source' => 'Repeat Order',
                'status' => 'Delivered',
                'subtotal' => 3200.00,
                'total' => 3200.00,
                'payment_status' => 'Paid',
                'created_at' => now()->subDays(1),
                'items' => [
                    ['product' => 'Tomato', 'qty' => 30, 'price' => 37.00, 'sub' => 1110.00],
                    ['product' => 'Onion', 'qty' => 40, 'price' => 28.00, 'sub' => 1120.00],
                    ['product' => 'Potato', 'qty' => 42, 'price' => 23.00, 'sub' => 970.00],
                ]
            ],
            [
                'customer' => 'ABC Traders',
                'order_number' => 'ORD-1254',
                'source' => 'WhatsApp',
                'status' => 'Delivered',
                'subtotal' => 2800.00,
                'total' => 2800.00,
                'payment_status' => 'Paid',
                'created_at' => now()->subDays(2),
                'items' => [
                    ['product' => 'Tomato', 'qty' => 20, 'price' => 37.00, 'sub' => 740.00],
                    ['product' => 'Carrot', 'qty' => 30, 'price' => 35.00, 'sub' => 1050.00],
                    ['product' => 'Cucumber', 'qty' => 31, 'price' => 32.00, 'sub' => 1010.00],
                ]
            ],
            [
                'customer' => 'ABC Traders',
                'order_number' => 'ORD-1248',
                'source' => 'Manual',
                'status' => 'Delivered',
                'subtotal' => 4150.00,
                'total' => 4150.00,
                'payment_status' => 'Paid',
                'created_at' => now()->subDays(5),
                'items' => [
                    ['product' => 'Ginger', 'qty' => 15, 'price' => 120.00, 'sub' => 1800.00],
                    ['product' => 'Garlic', 'qty' => 15, 'price' => 140.00, 'sub' => 2100.00],
                    ['product' => 'Green Chilli', 'qty' => 3.33, 'price' => 75.00, 'sub' => 250.00],
                ]
            ],
            [
                'customer' => 'Fresh Mart',
                'order_number' => 'ORD-1253',
                'source' => 'WhatsApp',
                'status' => 'Processing',
                'subtotal' => 5600.00,
                'total' => 5600.00,
                'payment_status' => 'Partially Paid',
                'created_at' => now()->subHours(2),
                'items' => [
                    ['product' => 'Tomato', 'qty' => 50, 'price' => 40.00, 'sub' => 2000.00],
                    ['product' => 'Onion', 'qty' => 60, 'price' => 30.00, 'sub' => 1800.00],
                    ['product' => 'Potato', 'qty' => 72, 'price' => 25.00, 'sub' => 1800.00],
                ]
            ],
            [
                'customer' => 'Green Leaf Hotel',
                'order_number' => 'ORD-1252',
                'source' => 'WhatsApp',
                'status' => 'Confirmed',
                'subtotal' => 4850.00,
                'total' => 4850.00,
                'payment_status' => 'Unpaid',
                'created_at' => now()->subHours(4),
                'items' => [
                    ['product' => 'Beans', 'qty' => 30, 'price' => 60.00, 'sub' => 1800.00],
                    ['product' => 'Capsicum', 'qty' => 25, 'price' => 65.00, 'sub' => 1625.00],
                    ['product' => 'Cauliflower', 'qty' => 31.66, 'price' => 45.00, 'sub' => 1425.00],
                ]
            ],
            [
                'customer' => 'Sree Stores',
                'order_number' => 'ORD-1251',
                'source' => 'Manual',
                'status' => 'Ready',
                'subtotal' => 1950.00,
                'total' => 1950.00,
                'payment_status' => 'Unpaid',
                'created_at' => now()->subHours(6),
                'items' => [
                    ['product' => 'Tomato', 'qty' => 25, 'price' => 40.00, 'sub' => 1000.00],
                    ['product' => 'Cucumber', 'qty' => 29.68, 'price' => 32.00, 'sub' => 950.00],
                ]
            ],
        ];

        foreach ($ordersSeed as $o) {
            $cust = $customerModels[$o['customer']] ?? null;
            if (!$cust) continue;

            $order = Order::firstOrCreate(
                ['order_number' => $o['order_number']],
                [
                    'customer_id' => $cust->id,
                    'whatsapp_account_id' => $wa1->id,
                    'source' => $o['source'],
                    'status' => $o['status'],
                    'subtotal' => $o['subtotal'],
                    'discount' => 0.00,
                    'delivery_charge' => 0.00,
                    'total_amount' => $o['total'],
                    'payment_status' => $o['payment_status'],
                    'payment_method' => 'Cash on Delivery',
                    'delivery_address' => $cust->address,
                    'delivery_date' => now()->addDay()->toDateString(),
                    'time_slot' => 'Morning (6:00 AM - 9:00 AM)',
                    'created_at' => $o['created_at'],
                    'created_by' => $admin->id,
                ]
            );

            foreach ($o['items'] as $item) {
                $p = $productModels[$item['product']] ?? null;
                OrderItem::firstOrCreate(
                    ['order_id' => $order->id, 'product_name' => $item['product']],
                    [
                        'product_id' => $p ? $p->id : null,
                        'unit' => $p ? $p->unit : 'kg',
                        'quantity' => $item['qty'],
                        'unit_price' => $item['price'],
                        'subtotal' => $item['sub'],
                    ]
                );
            }

            Delivery::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'driver_name' => 'Rashid Khan',
                    'driver_phone' => '+971 50 882 1940',
                    'vehicle_number' => 'DXB-VAN-4028',
                    'delivery_date' => now()->addDay()->toDateString(),
                    'time_slot' => 'Morning (6:00 AM - 9:00 AM)',
                    'status' => in_array($o['status'], ['Delivered', 'Ready', 'Confirmed']) ? $o['status'] : 'Pending',
                ]
            );

            if ($o['payment_status'] === 'Paid') {
                Payment::firstOrCreate(
                    ['order_id' => $order->id, 'customer_id' => $cust->id],
                    [
                        'payment_number' => 'PAY-' . rand(4000, 4999),
                        'amount' => $o['total'],
                        'payment_method' => 'Bank Transfer',
                        'reference_number' => 'TXN-' . strtoupper(bin2hex(random_bytes(4))),
                        'payment_date' => now()->subDay()->toDateString(),
                        'status' => 'Completed',
                        'received_by' => $accountsUser->id,
                    ]
                );
            }
        }

        // 9. Conversations and Realistic Chat Messages
        // Conversation 1: ABC Traders (Active Order Flow)
        $convAbc = Conversation::firstOrCreate(
            ['customer_id' => $abc->id, 'whatsapp_account_id' => $wa1->id],
            [
                'status' => 'bot_active',
                'bot_state' => 'ORDER_CREATED',
                'last_message' => '✅ Order confirmed. Order #ORD-1256 has been created.',
                'last_message_at' => now()->subMinutes(15),
                'unread_count' => 0,
            ]
        );

        $abcMessages = [
            ['sender' => 'customer', 'body' => 'Hi, I want to place an order.', 'time' => now()->subMinutes(20), 'meta' => null],
            ['sender' => 'bot', 'body' => "Hello! 👋 Welcome to FreshDeal Wholesale Vegetables.\n\nHow can we help you today?", 'time' => now()->subMinutes(19), 'meta' => null],
            ['sender' => 'customer', 'body' => 'I need tomato 20kg, onion 30kg and potato 60kg.', 'time' => now()->subMinutes(18), 'meta' => null],
            [
                'sender' => 'bot',
                'body' => "*Please Confirm Your Order*\n\n• *Tomato* — 20 kg @ ₹37.00/kg = *₹740.00*\n• *Onion* — 30 kg @ ₹28.00/kg = *₹840.00*\n• *Potato* — 60 kg @ ₹23.00/kg = *₹1,370.00*\n\n------------------------------------\nSubtotal                             ₹2,950.00\nDelivery                             FREE\n------------------------------------\n\n*Total*                                *₹2,950.00*",
                'time' => now()->subMinutes(17),
                'meta' => [
                    'quick_replies' => [
                        ['title' => '✅ Confirm Order', 'payload' => '1'],
                        ['title' => '✏️ Edit Order', 'payload' => 'edit'],
                        ['title' => '❌ Cancel Order', 'payload' => 'cancel'],
                    ]
                ]
            ],
        ];

        foreach ($abcMessages as $m) {
            Message::create([
                'conversation_id' => $convAbc->id,
                'sender_type' => $m['sender'],
                'body' => $m['body'],
                'metadata' => $m['meta'] ?? null,
                'created_at' => $m['time'],
                'is_read' => true,
            ]);
        }

        // Conversation 2: Fresh Mart
        $custFreshMart = $customerModels['Fresh Mart'];
        $convFreshMart = Conversation::firstOrCreate(
            ['customer_id' => $custFreshMart->id, 'whatsapp_account_id' => $wa1->id],
            [
                'status' => 'bot_active',
                'bot_state' => 'WELCOME',
                'last_message' => 'Please confirm our morning delivery dispatch time.',
                'last_message_at' => now()->subMinutes(32),
                'unread_count' => 1,
            ]
        );
        Message::create([
            'conversation_id' => $convFreshMart->id,
            'sender_type' => 'customer',
            'body' => 'Please confirm our morning delivery dispatch time.',
            'created_at' => now()->subMinutes(32),
            'is_read' => false,
        ]);

        // Conversation 3: Green Leaf Hotel (Human Handoff)
        $custGreenLeaf = $customerModels['Green Leaf Hotel'];
        $convGreenLeaf = Conversation::firstOrCreate(
            ['customer_id' => $custGreenLeaf->id, 'whatsapp_account_id' => $wa1->id],
            [
                'status' => 'human_required',
                'bot_state' => 'HUMAN_HANDOFF',
                'last_message' => 'Can we get 50kg baby carrots and imported celery tomorrow?',
                'last_message_at' => now()->subMinutes(45),
                'unread_count' => 2,
            ]
        );
        Message::create([
            'conversation_id' => $convGreenLeaf->id,
            'sender_type' => 'customer',
            'body' => 'Need vegetables tomorrow. Can we get 50kg baby carrots and imported celery tomorrow?',
            'created_at' => now()->subMinutes(45),
            'is_read' => false,
        ]);

        // Conversation 4: Sree Stores
        $custSree = $customerModels['Sree Stores'];
        $convSree = Conversation::firstOrCreate(
            ['customer_id' => $custSree->id, 'whatsapp_account_id' => $wa1->id],
            [
                'status' => 'bot_active',
                'bot_state' => 'CONFIRM_ORDER',
                'last_message' => 'Need 25kg tomato and 30kg cucumber.',
                'last_message_at' => now()->subHours(1),
                'unread_count' => 0,
            ]
        );

        // 10. Expenses
        Expense::create([
            'category' => 'Fuel & Transport',
            'amount' => 1450.00,
            'expense_date' => now()->toDateString(),
            'payment_method' => 'Cash',
            'description' => 'Diesel refuel for DXB delivery vans #4028 & #4029',
            'recorded_by' => $admin->id,
        ]);
        Expense::create([
            'category' => 'Packaging Materials',
            'amount' => 3200.00,
            'expense_date' => now()->subDays(2)->toDateString(),
            'payment_method' => 'Bank Transfer',
            'description' => '500 Wholesale vegetable crates and protective liners',
            'recorded_by' => $admin->id,
        ]);
    }
}
