<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get all dashboard statistics.
     */
    public function index()
    {
        return ApiResponse::success([
            'todays_revenue' => $this->getTodaysRevenue(),
            'todays_transactions' => $this->getTodaysTransactions(),
            'products_sold' => $this->getProductsSoldToday(),
            'revenue_chart' => $this->getRevenueChart(),
            'best_selling_products' => $this->getBestSellingProducts(),
            'low_stock_alerts' => $this->getLowStockAlerts(),
        ], 'Dashboard statistics retrieved successfully.');
    }

    /**
     * 1. Today's Revenue - sum of total from today's transactions.
     */
    private function getTodaysRevenue(): array
    {
        $revenue = Transaction::whereDate('created_at', Carbon::today())
            ->sum('total');

        return [
            'total' => (float) $revenue,
        ];
    }

    /**
     * 2. Today's Transactions - count of transactions created today.
     */
    private function getTodaysTransactions(): array
    {
        $count = Transaction::whereDate('created_at', Carbon::today())
            ->count();

        return [
            'count' => $count,
        ];
    }

    /**
     * 3. Products Sold Today - total quantity of items sold today.
     */
    private function getProductsSoldToday(): array
    {
        $totalQuantity = TransactionItem::whereHas('transaction', function ($query) {
            $query->whereDate('created_at', Carbon::today());
        })->sum('quantity');

        return [
            'total_quantity' => (int) $totalQuantity,
        ];
    }

    /**
     * 4. Revenue Chart - daily revenue for the last 30 days.
     */
    private function getRevenueChart(): array
    {
        $startDate = Carbon::today()->subDays(29);
        $endDate = Carbon::today();

        $revenues = Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total) as total_revenue')
        )
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill in missing dates with 0 revenue
        $chart = [];
        $currentDate = Carbon::today()->subDays(29);

        for ($i = 0; $i < 30; $i++) {
            $dateStr = $currentDate->format('Y-m-d');
            $chart[] = [
                'date' => $dateStr,
                'revenue' => (float) ($revenues[$dateStr]->total_revenue ?? 0),
            ];
            $currentDate->addDay();
        }

        return $chart;
    }

    /**
     * 5. Best Selling Products - top 5 products by quantity sold (all time).
     */
    private function getBestSellingProducts(): array
    {
        $products = TransactionItem::select(
            'product_id',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(subtotal) as total_revenue')
        )
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->with('product:id,name,image,price')
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name,
                    'product_image' => $item->product?->image,
                    'product_price' => (float) ($item->product?->price ?? 0),
                    'total_quantity' => (int) $item->total_quantity,
                    'total_revenue' => (float) $item->total_revenue,
                ];
            })
            ->toArray();

        return $products;
    }

    /**
     * 6. Low Stock Alert - products with stock <= 10.
     */
    private function getLowStockAlerts(): array
    {
        $products = Product::where('stock', '<', 10)
            ->orderBy('stock')
            ->limit(10)
            ->get(['id', 'name', 'image', 'price', 'stock'])
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->image,
                    'price' => (float) $product->price,
                    'stock' => $product->stock,
                ];
            })
            ->toArray();

        return $products;
    }
}
