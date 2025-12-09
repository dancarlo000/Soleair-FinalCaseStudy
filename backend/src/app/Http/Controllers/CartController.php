<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    // READ: View User's Cart
    public function index()
    {
        $userId = Auth::id();
        
        // Load cart items for the logged-in user, including related product details
        $cartItems = Cart::where('user_id', $userId)
                         ->with('product') // Assumes Cart model has a 'product' relationship
                         ->get();
        
        return response()->json($cartItems, 200);
    }

    // CREATE: Add or Increment Item in Cart
    public function store(Request $request)
    {
        // 1. Validate incoming data
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'size' => 'nullable|string|max:50',     // Allow variants
            'color' => 'nullable|string|max:50',    // Allow variants
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'User must be authenticated to use cart'], 401);
        }
        
        // 2. Check if product exists and get stock
        $product = Product::find($validated['product_id']);
        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        // 3. Find existing cart item matching product, size, and color
        $cartItem = Cart::where('user_id', $userId)
                        ->where('product_id', $validated['product_id'])
                        ->where('size', $validated['size'] ?? null) // Match variants
                        ->where('color', $validated['color'] ?? null) // Match variants
                        ->first();

        $newQuantity = $validated['quantity'];

        if ($cartItem) {
            // If exists, increment quantity
            $newQuantity = $cartItem->quantity + $validated['quantity'];
            
            // Check stock limit (important for merging)
            if ($newQuantity > $product->quantity) {
                 return response()->json(['message' => 'Maximum stock exceeded.'], 400);
            }
            
            $cartItem->quantity = $newQuantity;
            $cartItem->save();
        } else {
            // Create new entry
            if ($newQuantity > $product->quantity) {
                 return response()->json(['message' => 'Insufficient stock.'], 400);
            }
            
            $cartItem = Cart::create([
                'user_id' => $userId,
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'size' => $validated['size'] ?? null,
                'color' => $validated['color'] ?? null,
            ]);
        }

        // Return the full cart item with product relationship loaded for easy frontend update
        $cartItem->load('product');

        return response()->json(['message' => 'Added to cart', 'data' => $cartItem], 201);
    }

    // UPDATE: Change Quantity
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $userId = Auth::id();
        $cartItem = Cart::where('user_id', $userId)->where('id', $id)->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Item not found in your cart'], 404);
        }
        
        // Check stock limit (Uses the product relation to get stock)
        if ($validated['quantity'] > $cartItem->product->quantity) {
            return response()->json(['message' => 'Maximum stock exceeded.'], 400);
        }

        $cartItem->quantity = $validated['quantity'];
        $cartItem->save();

        return response()->json(['message' => 'Cart updated', 'data' => $cartItem->load('product')], 200);
    }

    // DELETE: Remove Item
    public function destroy($id)
    {
        $userId = Auth::id();
        $cartItem = Cart::where('user_id', $userId)->where('id', $id)->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Item not found in your cart'], 404);
        }

        $cartItem->delete();

        return response()->json(['message' => 'Item removed from cart'], 200);
    }
}