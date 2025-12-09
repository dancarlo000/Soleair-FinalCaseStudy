<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * CLIENT: Display a listing of the user's orders.
     */
    public function index()
    {
        $userId = Auth::id();

        $orders = Order::where('user_id', $userId)
                       ->with(['items.product'])
                       ->orderByDesc('created_at')
                       ->get();

        return response()->json($orders, 200);
    }

    /**
     * CLIENT: Cancel an order
     */
    public function cancel($id)
    {
        $user = Auth::user();
        
        // Find the order belonging to the authenticated user
        $order = Order::where('id', $id)
                      ->where('user_id', $user->id)
                      ->with('items')
                      ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Only allow cancellation if status is processing or pending
        if ($order->status !== 'processing' && $order->status !== 'pending') {
            return response()->json(['message' => 'Order cannot be cancelled'], 400);
        }

        DB::beginTransaction();
        try {
            // 1. Restore Stock for each item in the order
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    // Use 'quantity' column as per your database schema
                    $product->increment('quantity', $item->quantity);
                }
            }

            // 2. Update Order Status to 'cancelled'
            $order->status = 'cancelled';
            $order->save();

            DB::commit();
            return response()->json(['message' => 'Order cancelled successfully', 'order' => $order], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Cancellation failed', 'error' => $e->getMessage()], 500);
        }
    }

    // --- ADMIN METHODS ---

    /**
     * ADMIN: Get ALL orders from ALL users
     */
    public function getAllOrders()
    {
        // In a real app, add a check: if (!auth()->user()->is_admin) abort(403);
        
        $orders = Order::with(['user', 'items.product']) // Include User info to know who bought it
                       ->orderByDesc('created_at')
                       ->get();

        return response()->json($orders, 200);
    }

    /**
     * ADMIN: Update Order Status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json(['message' => 'Order status updated', 'order' => $order], 200);
    }

    /**
     * ADMIN: Get Sales Analytics (Daily & Total)
     */
    public function getSalesAnalytics()
    {
        // 1. Total All-Time Revenue (excluding cancelled orders)
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');

        // 2. Daily Sales Breakdown
        // Groups orders by date (YYYY-MM-DD) to see performance over time
        $dailySales = Order::selectRaw('DATE(created_at) as date, SUM(total_amount) as total_sales, COUNT(*) as order_count')
                           ->where('status', '!=', 'cancelled')
                           ->groupBy('date')
                           ->orderBy('date', 'desc')
                           ->get();

        return response()->json([
            'total_revenue' => $totalRevenue,
            'daily_sales' => $dailySales
        ], 200);
    }
}