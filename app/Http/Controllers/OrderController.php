<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Customer should only see their own orders
        $orders = auth()->user()->orders ?? Order::where('users_id', auth()->id())->get();
        return response()->json($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        // Customer should only see their own order
        if ($order->users_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {

        $request->validate([
            'status' => 'required|in:pending,prepared,delivered,cancelled'
        ]);

        if (!in_array(auth()->user()->role, ['admin', 'employee'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->update(['status' => $request->status]);

        return response()->json($order);
    }

    public function cancel(Order $order)
    {
        if ($order->users_id !== auth()->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Order cannot be canceled because it is already being prepared or delivered.'], 400);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Order successfully canceled', 'order' => $order]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
