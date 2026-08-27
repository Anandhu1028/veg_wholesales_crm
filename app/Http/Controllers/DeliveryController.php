<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Delivery;
use App\Models\Order;
use App\Services\DeliveryService;
use Illuminate\Support\Facades\Auth;

class DeliveryController extends Controller
{
    public function __construct(
        protected DeliveryService $deliveryService
    ) {}

    public function index(Request $request)
    {
        $query = Delivery::with(['order.customer', 'order.orderItems'])
            ->latest('delivery_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $deliveries = $query->paginate(15);

        $statusCounts = [
            'Pending' => Delivery::where('status', 'Pending')->count(),
            'Preparing' => Delivery::where('status', 'Preparing')->count(),
            'Ready' => Delivery::where('status', 'Ready')->count(),
            'Out for Delivery' => Delivery::where('status', 'Out for Delivery')->count(),
            'Delivered' => Delivery::where('status', 'Delivered')->count(),
        ];

        return view('deliveries.index', compact('deliveries', 'statusCounts'));
    }

    public function updateStatus(Request $request, Delivery $delivery)
    {
        $request->validate([
            'status' => 'required|in:Pending,Preparing,Ready,Out for Delivery,Delivered,Failed',
            'notes' => 'nullable|string',
        ]);

        $this->deliveryService->updateDeliveryStatus($delivery, $request->status, $request->notes, Auth::id());

        return back()->with('success', "Delivery status updated to {$request->status}.");
    }

    public function assignDriver(Request $request, Delivery $delivery)
    {
        $request->validate([
            'driver_name' => 'required|string',
            'driver_phone' => 'nullable|string',
            'vehicle_number' => 'nullable|string',
        ]);

        $delivery->update([
            'driver_name' => $request->driver_name,
            'driver_phone' => $request->driver_phone,
            'vehicle_number' => $request->vehicle_number,
        ]);

        return back()->with('success', 'Driver details updated.');
    }
}
