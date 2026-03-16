<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class StatisticsController extends Controller
{
    /**
     * Get overall statistics about sales, popular products, and categories.
     */
    public function index()
    {
        // Total sales (sum of unit_price * quantity from completed orders if we had completed status, let's just use all order items for now depending on order status)
        $totalSales = DB::table('order_items')
            ->join('orders', 'order_items.orders_id', '=', 'orders.id')
            ->select(DB::raw('SUM(order_items.quantity * order_items.unit_price) as total_sales'))
            ->whereIn('orders.status', ['delivered', 'prepared', 'pending']) // Or just don't filter if you want all
            ->first()
            ->total_sales ?? 0;

        // Most popular products (top 5 by quantity sold)
        $popularProducts = DB::table('order_items')
            ->join('products', 'order_items.products_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_quantity'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        // Distribution by category
        $categoryDistribution = DB::table('order_items')
            ->join('products', 'order_items.products_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name as category_name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_sold')
            ->get();

        return response()->json([
            'total_sales' => $totalSales,
            'popular_products' => $popularProducts,
            'category_distribution' => $categoryDistribution,
        ]);
    }
}
