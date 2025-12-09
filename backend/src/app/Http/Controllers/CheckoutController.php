<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $user = Auth::user(); 
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $request->validate([
            'shipping_address' => 'required|string',
            'phone' => 'required|string',
            'name' => 'required|string',
            'payment_method' => 'required|string',
            'direct_item' => 'nullable|array' // Validate optional direct item
        ]);

        $itemsToProcess = [];

        // --- LOGIC: Check for Direct Buy vs Cart ---
        if ($request->has('direct_item') && !empty($request->direct_item)) {
            // CASE A: "Buy Now" (Process single item, ignore Cart table)
            $directData = $request->direct_item;
            
            $product = Product::find($directData['product_id']);
            if(!$product) return response()->json(['message' => 'Product not found'], 404);

            // Create a pseudo-object to mimic the Cart model structure
            $pseudoItem = new \stdClass();
            $pseudoItem->product_id = $product->id;
            $pseudoItem->quantity = $directData['quantity'];
            $pseudoItem->size = $directData['size'] ?? null;
            $pseudoItem->color = $directData['color'] ?? null;
            $pseudoItem->product = $product; // Attach relation manually

            $itemsToProcess[] = $pseudoItem;

        } else {
            // CASE B: Standard Checkout (Process items from DB Cart)
            $cartItems = Cart::with('product')->where('user_id', $user->id)->get();
            
            if ($cartItems->isEmpty()) {
                return response()->json(['message' => 'Cart is empty'], 422);
            }
            $itemsToProcess = $cartItems;
        }

        DB::beginTransaction();

        try {
            $total = 0;
            
            // 1. Calculate Total & Check Stock
            foreach ($itemsToProcess as $item) {
                $product = $item->product;
                
                if (!$product) {
                     throw new \Exception("Product data missing.");
                }
                
                // Check Stock (using 'quantity' column)
                if ($item->quantity > $product->quantity) {
                    throw new \Exception("Not enough stock for product {$product->name}. Only {$product->quantity} available.");
                }
                
                // Calculate Price (Check for discount)
                $price = $product->discount > 0 
                    ? $product->price * (1 - $product->discount / 100) 
                    : $product->price;

                $total += $price * $item->quantity; 
            }

            // 2. Create Order Record
            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => $total,
                'status' => 'processing', 
                'shipping_address' => $request->shipping_address,
                'payment_method' => $request->payment_method,
            ]);

            // 3. Create Order Items & Decrement Stock
            foreach ($itemsToProcess as $item) {
                $product = $item->product;
                
                // Recalculate price for the record
                $price = $product->discount > 0 
                    ? $product->price * (1 - $product->discount / 100) 
                    : $product->price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'price' => $price, 
                    'size' => $item->size ?? null, 
                    'color' => $item->color ?? null, 
                ]);

                // Decrement Stock
                $product->decrement('quantity', $item->quantity);
            }

            // 4. Clear Cart (ONLY if this was a Cart Checkout)
            if (!$request->has('direct_item') || empty($request->direct_item)) {
                Cart::where('user_id', $user->id)->delete();
            }

            DB::commit();

            return response()->json(['message' => 'Order placed successfully', 'order' => $order->load('items.product')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Checkout failed', 'error' => $e->getMessage()], 500);
        }
    }
}