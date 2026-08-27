<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'items.product', 'creator'])
            ->latest('order_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $purchases = $query->paginate(15);
        $totalPurchases = PurchaseOrder::where('status', 'Received')->sum('total_amount');
        $pendingPOs = PurchaseOrder::whereIn('status', ['Draft', 'Ordered'])->count();

        return view('purchases.index', compact('purchases', 'totalPurchases', 'pendingPOs'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('company_name')->get();
        $products = Product::where('status', 'active')->orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0.01',
        ]);

        $latestPO = PurchaseOrder::latest('id')->first();
        $poNum = 'PO-' . str_pad(($latestPO ? $latestPO->id + 8021 : 8021), 4, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($request, $poNum) {
            $subtotal = 0;
            $po = PurchaseOrder::create([
                'po_number' => $poNum,
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'status' => $request->status ?: 'Ordered',
                'notes' => $request->notes,
                'created_by' => Auth::id(),
                'total_amount' => 0,
            ]);

            foreach ($request->items as $item) {
                $prod = Product::find($item['product_id']);
                $qty = (float)$item['quantity'];
                $price = (float)$item['unit_price'];
                $lineSub = round($qty * $price, 2);
                $subtotal += $lineSub;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $prod->id,
                    'product_name' => $prod->name,
                    'unit' => $prod->unit,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $lineSub,
                ]);

                // If already received on creation
                if ($po->status === 'Received') {
                    $this->inventoryService->recordTransaction(
                        $prod->id,
                        'purchase',
                        $qty,
                        "Received via PO {$poNum}",
                        'PurchaseOrder',
                        $po->id,
                        Auth::id()
                    );
                }
            }

            $po->update(['total_amount' => $subtotal]);

            // Update supplier balance
            $po->supplier->increment('outstanding_balance', $subtotal);
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase order created successfully.');
    }

    public function show(PurchaseOrder $purchase)
    {
        $purchase->load(['supplier', 'items.product', 'creator']);
        return view('purchases.show', compact('purchase'));
    }

    public function updateStatus(Request $request, PurchaseOrder $purchase)
    {
        $request->validate([
            'status' => 'required|in:Draft,Ordered,Received,Cancelled',
        ]);

        $oldStatus = $purchase->status;
        $newStatus = $request->status;

        DB::transaction(function () use ($purchase, $oldStatus, $newStatus) {
            $purchase->update([
                'status' => $newStatus,
                'received_date' => $newStatus === 'Received' ? now()->toDateString() : $purchase->received_date,
            ]);

            if ($newStatus === 'Received' && $oldStatus !== 'Received') {
                foreach ($purchase->items as $item) {
                    if ($item->product_id) {
                        $this->inventoryService->recordTransaction(
                            $item->product_id,
                            'purchase',
                            (float)$item->quantity,
                            "Goods received for {$purchase->po_number}",
                            'PurchaseOrder',
                            $purchase->id,
                            Auth::id()
                        );
                    }
                }
            }
        });

        return redirect()->route('purchases.show', $purchase)->with('success', "PO status updated to {$newStatus}.");
    }
}
