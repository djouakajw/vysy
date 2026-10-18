<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function emailLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
            'name' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:2',
            'language' => 'nullable|string|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = $request->email;
        $password = $request->password;
        $name = $request->name ?? explode('@', $email)[0];

        $user = User::firstWhere('email', $email);

        if (!$user) {
            // Create account
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'country' => $request->country ?? 'CM',
                'language' => $request->language ?? 'fr',
                'is_active' => true,
            ]);
        } elseif (!Hash::check($password, $user->password)) {
            return response()->json(['error => invalid_credentials'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['error' => 'account_disabled'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'requires_password_setup' => false,
        ]);
    }

    public function googleLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'name' => 'required|string',
            'image' => 'nullable|url',
            'google_id' => 'required|string',
            'candidate_subdomain' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::firstWhere('email', $request->email)
            ?? User::firstWhere('google_id', $request->google_id);

        if (!$user) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make(Str::random(24)),
                'google_id' => $request->google_id,
                'avatar' => $request->image,
                'country' => 'CM',
                'language' => 'fr',
                'is_active' => true,
            ]);
        }

        if (!$user->google_id) {
            $user->update(['google_id' => $request->google_id, 'avatar' => $request->image]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'draftId' => null,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
