<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\InventoryTransaction;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $products = Product::orderBy('name')->get();
        $transactions = InventoryTransaction::with(['product', 'creator'])
            ->latest()
            ->paginate(20);

        $totalItems = Product::count();
        $lowStockCount = Product::whereRaw('stock_quantity <= low_stock_threshold')->count();
        $totalStockKg = Product::sum('stock_quantity');

        return view('inventory.index', compact('products', 'transactions', 'totalItems', 'lowStockCount', 'totalStockKg'));
    }

    public function adjust(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:purchase,sale,wastage,damage,adjustment_in,adjustment_out,return,stock_in',
            'quantity' => 'required|numeric|min:0.1',
            'notes' => 'nullable|string',
        ]);

        $this->inventoryService->recordTransaction(
            $request->product_id,
            $request->type,
            (float)$request->quantity,
            $request->notes,
            'ManualAdjustment',
            null,
            Auth::id()
        );

        return redirect()->route('inventory.index')->with('success', 'Stock adjustment recorded successfully.');
    }
}
