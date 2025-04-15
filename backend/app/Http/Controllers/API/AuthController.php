<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param  \App\Http\Requests\Auth\RegisterRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone_number' => $validated['phone_number'] ?? null,
        ]);

        // Assign default user role
        $userRole = Role::where('name', 'User')->first();
        if ($userRole) {
            $user->roles()->attach($userRole);
        }

        // Create access token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Create refresh token
        $refreshToken = $user->createToken('refresh_token', ['refresh'])->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Login user and create token.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        if (!Auth::attempt($validated)) {
            return response()->json([
                'message' => 'Invalid login credentials'
            ], 401);
        }

        $user = User::where('email', $validated['email'])->firstOrFail();

        // Delete previous tokens
        $user->tokens()->delete();

        // Create new access token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Create refresh token
        $refreshToken = $user->createToken('refresh_token', ['refresh'])->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->load('roles'),
            'token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout user and revoke token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // Check if a refresh token was provided
        $refreshToken = $request->refresh_token;

        if ($refreshToken) {
            // Extract the token ID from the refresh token
            $tokenId = explode('|', $refreshToken)[0] ?? null;

            if ($tokenId) {
                // Find and delete the specific refresh token
                PersonalAccessToken::where('id', $tokenId)->where('tokenable_id', $request->user()->id)->delete();
            }
        } else {
            // If no specific token provided, delete all tokens
            $request->user()->tokens()->delete();
        }

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Refresh the access token using a refresh token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string'
        ]);

        // Extract the token ID and hash from the refresh token
        $refreshToken = $request->refresh_token;
        $tokenId = explode('|', $refreshToken)[0] ?? null;

        if (!$tokenId) {
            return response()->json([
                'message' => 'Invalid refresh token format'
            ], 401);
        }

        // Find the token in the database
        $token = PersonalAccessToken::find($tokenId);

        if (!$token || !$token->can('refresh')) {
            return response()->json([
                'message' => 'Invalid refresh token'
            ], 401);
        }

        // Get the user associated with the token
        $user = $token->tokenable;

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 401);
        }

        // Create a new access token
        $newAccessToken = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $newAccessToken,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Get authenticated user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('roles'),
        ]);
    }

    /**
     * Send password reset link to the given user.
     *
     * @param  \App\Http\Requests\Auth\ForgotPasswordRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $validated = $request->validated();

        // Send the password reset link using Laravel's built-in Password Broker
        $status = Password::sendResetLink(
            ['email' => $validated['email']]
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link has been sent to your email address'
            ]);
        }

        return response()->json([
            'message' => 'Unable to send password reset link'
        ], 400);
    }

    /**
     * Reset user's password using the provided token.
     *
     * @param  \App\Http\Requests\Auth\ResetPasswordRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $validated = $request->validated();

        // Reset the user's password using Laravel's built-in Password Broker
        $status = Password::reset(
            $validated,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password has been successfully reset'
            ]);
        }

        return response()->json([
            'message' => 'Unable to reset password',
            'errors' => ['token' => 'Invalid or expired token']
        ], 400);
    }
}
