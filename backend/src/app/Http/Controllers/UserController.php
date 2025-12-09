<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Get all users (customers)
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return response()->json($users, 200);
    }

    // Block or Unblock a user
    public function toggleBlock(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'is_blocked' => 'required|boolean'
        ]);

        $user->is_blocked = $request->input('is_blocked');
        $user->save();

        $status = $user->is_blocked ? 'blocked' : 'unblocked';

        return response()->json([
            'message' => "User has been successfully {$status}.",
            'user' => $user
        ], 200);
    }

    // NEW: Update User Profile (Address & Phone)
    public function updateProfile(Request $request)
    {
        $user = Auth::user(); // Get the currently authenticated user
        
        $request->validate([
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        // Update fields
        $user->address = $request->address;
        $user->phone = $request->phone;
        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully', 
            'user' => $user
        ], 200);
    }
}