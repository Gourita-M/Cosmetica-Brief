<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\DAO\StatisticsDAO;

class StatisticsController extends Controller
{
    protected $statisticsDAO;

    public function __construct(StatisticsDAO $statisticsDAO)
    {
        $this->statisticsDAO = $statisticsDAO;
    }

    /**
     * Get overall statistics about sales, popular products, and categories.
     */
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Only administrators can view statistics.'], 403);
        }
        
        $totalSales = $this->statisticsDAO->getTotalSales();
        $popularProducts = $this->statisticsDAO->getPopularProducts();
        $categoryDistribution = $this->statisticsDAO->getCategoryDistribution();

        return response()->json([
            'total_sales' => $totalSales,
            'popular_products' => $popularProducts,
            'category_distribution' => $categoryDistribution,
        ]);
    }
}
