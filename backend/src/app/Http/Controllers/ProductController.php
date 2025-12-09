<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Import Storage facade for file handling

class ProductController extends Controller
{
    // GET ALL PRODUCTS (Modified for Sorting)
    public function index(Request $request)
    {
        $query = Product::query();

        // Handle Sorting
        if ($request->has('sort')) {
            if ($request->sort === 'price_asc') {
                $query->orderBy('price', 'asc');
            } elseif ($request->sort === 'price_desc') {
                $query->orderBy('price', 'desc');
            } elseif ($request->sort === 'newest') {
                $query->orderBy('created_at', 'desc');
            }
        } else {
            // Default sort
            $query->orderBy('created_at', 'desc');
        }

        return response()->json($query->get(), 200);
    }

    // GET SINGLE PRODUCT
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['message' => 'Product not found'], 404);
        return response()->json($product, 200);
    }

    // CREATE (Store)
    public function store(Request $request)
    {
        // Validation: Ensure all Admin Panel fields are present
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'price' => 'required|numeric',
            'category' => 'required|string',
            'quantity' => 'required|integer',
            'image' => 'required', // Can be string or file
            'description' => 'required|string',
            'discount' => 'nullable|integer|min:0|max:100', // <--- ADDED THIS
        ]);

        // Handle File Upload
        if ($request->hasFile('image')) {
            // Stores in /storage/app/public/products
            $path = $request->file('image')->store('products', 'public');
            // Save relative path: "products/filename.jpg"
            $validatedData['image'] = $path;
        }

        // Create the product (The Model will auto-generate the slug)
        $product = Product::create($validatedData);

        return response()->json($product, 201);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Validation: 'sometimes' means only validate if the field is present
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'brand' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric',
            'category' => 'sometimes|required|string',
            'quantity' => 'sometimes|required|integer',
            'image' => 'sometimes', // Can be string or file
            'description' => 'sometimes|required|string',
            'discount' => 'nullable|integer|min:0|max:100', // <--- ADDED THIS
        ]);

        // Handle Image Update
        if ($request->hasFile('image')) {
            // Delete old image if it exists and isn't a URL (to save space)
            if ($product->image && !filter_var($product->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($product->image);
            }
            
            $path = $request->file('image')->store('products', 'public');
            $validatedData['image'] = $path;
        }

        $product->update($validatedData);

        return response()->json($product, 200);
    }

    // DELETE
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['message' => 'Product not found'], 404);
        
        // Optional: Delete image from storage when deleting product
        if ($product->image && !filter_var($product->image, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        
        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}