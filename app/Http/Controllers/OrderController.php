<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\DAO\OrderDAO;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderDAO;

    public function __construct(OrderDAO $orderDAO)
    {
        $this->orderDAO = $orderDAO;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        if (in_array($user->role, ['admin', 'employee'])) {
            $orders = $this->orderDAO->getAllOrders();
        } else {
            $orders = $user->orders ?? $this->orderDAO->getUserOrders($user->id);
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

            $product = $this->orderDAO->getProductBySlug($item['slug']);
            
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

        $order = $this->orderDAO->createPendingOrder(auth()->user()->id);

        foreach ($orderItems as $item) {
            $this->orderDAO->createOrderItemAndDecrementStock($order, $item);
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

        $this->orderDAO->updateOrderStatus($order, $request->status);

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

        $this->orderDAO->updateOrderStatus($order, 'cancelled');

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
