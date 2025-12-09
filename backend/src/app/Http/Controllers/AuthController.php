<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // <--- Added for DB table access
use Illuminate\Support\Str;        // <--- Added for token generation
use Carbon\Carbon;                 // <--- Added for timestamps

class AuthController extends Controller
{
    // REGISTER
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    // LOGIN
    public function login(Request $request)
    {
        $loginInput = $request->input('email');
        $password = $request->input('password');

        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (!Auth::attempt([$field => $loginInput, 'password' => $password])) {
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        $user = User::where($field, $loginInput)->firstOrFail();
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }
    
    // LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    // --- FORGOT PASSWORD (DEV MODE) ---
    public function forgotPassword(Request $request)
    {
        // 1. Validate email exists
        $request->validate(['email' => 'required|email|exists:users,email']);

        // 2. Generate a random token
        $token = Str::random(60);

        // 3. Save to password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => Carbon::now()]
        );

        // 4. Return the token directly
        return response()->json([
            'message' => 'Reset token generated successfully.',
            'token' => $token 
        ], 200);
    }

    // --- RESET PASSWORD ---
    public function resetPassword(Request $request)
    {
        // 1. Validate input
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed', 
        ]);

        // 2. Check if token matches
        $resetRecord = DB::table('password_reset_tokens')
                            ->where('email', $request->email)
                            ->where('token', $request->token)
                            ->first();

        if (!$resetRecord) {
            return response()->json(['message' => 'Invalid token or email.'], 400);
        }

        // 3. Update User Password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // 4. Delete token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password reset successfully. You can now login.'], 200);
    }

    // --- NEW: CHANGE PASSWORD REQUEST (LOGGED IN) ---
    public function requestPasswordChange(Request $request)
    {
        $user = Auth::user(); // Get authenticated user
        $token = Str::random(60);
        
        // Reuse password_reset_tokens table logic
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => $token, 'created_at' => Carbon::now()]
        );

        return response()->json([
            'message' => 'Change password token generated.',
            'token' => $token // Developer convenience (simulated email)
        ], 200);
    }

    // --- NEW: CHANGE PASSWORD CONFIRM (LOGGED IN) ---
    public function changePassword(Request $request)
    {
        $user = Auth::user(); // Get authenticated user
        
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Verify token matches the logged-in user's email
        $resetRecord = DB::table('password_reset_tokens')
                            ->where('email', $user->email)
                            ->where('token', $request->token)
                            ->first();

        if (!$resetRecord) {
            return response()->json(['message' => 'Invalid token.'], 400);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Cleanup token
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        return response()->json(['message' => 'Password changed successfully.'], 200);
    }
}