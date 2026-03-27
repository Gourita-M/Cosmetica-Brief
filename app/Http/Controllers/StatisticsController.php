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
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Only administrators can view statistics.'], 403);
        }
        
        $totalSales = DB::table('orders_items')
            ->join('orders', 'orders_items.orders_id', '=', 'orders.id')
            ->select(DB::raw('SUM(orders_items.quantity * orders_items.unit_price) as total_sales'))
            ->whereIn('orders.status', ['delivered', 'prepared', 'pending'])
            ->first()
            ->total_sales ?? 0;

        $popularProducts = DB::table('orders_items')
            ->join('products', 'orders_items.products_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(orders_items.quantity) as total_quantity'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        $categoryDistribution = DB::table('orders_items')
            ->join('products', 'orders_items.products_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name as category_name', DB::raw('SUM(orders_items.quantity) as total_sold'))
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
