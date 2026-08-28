<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\WhatsAppWebhookController;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/api/whatsapp/webhook', [WhatsAppWebhookController::class, 'verify'])
    ->name('whatsapp.webhook.verify');

// Protected Application Routes
Route::middleware('auth')->group(function () {
    // 1. Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Common Inbox & Simulated WhatsApp
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox');
    Route::post('/inbox/simulate', [InboxController::class, 'simulateIncoming'])->name('inbox.simulate');
    Route::post('/inbox/{conversation}/send', [InboxController::class, 'sendMessage'])->name('inbox.send');
    Route::post('/inbox/{conversation}/toggle-handoff', [InboxController::class, 'toggleHandoff'])->name('inbox.toggle-handoff');
    Route::post('/inbox/{conversation}/star', [InboxController::class, 'toggleStar'])->name('inbox.star');

    // 3. Orders
    Route::resource('orders', OrderController::class);
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/repeat', [OrderController::class, 'repeat'])->name('orders.repeat');
    Route::post('/orders/{order}/record-payment', [OrderController::class, 'recordPayment'])->name('orders.record-payment');

    // 4. Customers
    Route::resource('customers', CustomerController::class);
    Route::post('/customers/{customer}/custom-price', [CustomerController::class, 'updateCustomPrice'])->name('customers.custom-price');

    // 5. Products
    Route::resource('products', ProductController::class);

    // 6. Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

    // 7. Suppliers
    Route::resource('suppliers', SupplierController::class);

    // 8. Purchases
    Route::resource('purchases', PurchaseController::class);
    Route::post('/purchases/{purchase}/status', [PurchaseController::class, 'updateStatus'])->name('purchases.update-status');

    // 9. Deliveries
    Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::post('/deliveries/{delivery}/status', [DeliveryController::class, 'updateStatus'])->name('deliveries.update-status');
    Route::post('/deliveries/{delivery}/driver', [DeliveryController::class, 'assignDriver'])->name('deliveries.assign-driver');

    // 10. Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');

    // 11. Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // 12. WhatsApp Settings & Accounts
    Route::get('/whatsapp', [WhatsAppController::class, 'index'])->name('whatsapp.index');
    Route::put('/whatsapp/{whatsapp}', [WhatsAppController::class, 'update'])->name('whatsapp.update');

    // 13. Staff
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');

    // Settings fallback
    Route::get('/settings', function () {
        return redirect()->route('whatsapp.index');
    })->name('settings.index');
});
