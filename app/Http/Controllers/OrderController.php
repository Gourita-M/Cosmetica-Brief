<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        if (in_array($user->role, ['admin', 'employee'])) {
            $orders = Order::all();
        } else {
      
            $orders = $user->orders ?? Order::where('users_id', $user->id)->get();
        }

        return response()->json($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();

        $totalAmount = 0;
        $orderItems = [];

        foreach ($validated['products'] as $item) {

            $product = Product::where('slug', $item['slug'])->first();
            
            if (!$product) {
                return response()->json(['message' => 'Product ' . $item['slug'] . ' not found'], 404);
            }

            if ($product->stock < $item['quantity']) {
                return response()->json(['message' => 'Not enough stock for ' . $product->name], 400);
            }

            $orderItems[] = [
                'products_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
            ];
            
            $totalAmount += $product->price * $item['quantity'];
        }

        $order = Order::create([
            'users_id' => auth()->user()->id,
            'status' => 'pending',
        ]);

        foreach ($orderItems as $item) {
            $order->items()->create($item);
            
            $product = \App\Models\Product::find($item['products_id']);
            $product->decrement('stock', $item['quantity']);
        }

        return response()->json([
            'message' => 'Order placed successfully',
            'order' => $order->load('items.product'),
            'total_amount' => $totalAmount
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $user = auth()->user();

        if ($order->users_id !== $user->id && !in_array($user->role, ['admin', 'employee'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
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
        $user = auth()->user();

        if ($order->users_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized. Only the order owner can cancel their order.'], 403);
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
