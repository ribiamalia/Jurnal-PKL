<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; // For validating data
use Tymon\JWTAuth\Facades\JWTAuth; // For getting JWT token

class LoginController extends Controller
{
    /**
     * Handle user login and generate JWT token.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Set validation rules
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'password' => 'required',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Get "name" and "password" from input
        $credentials = $request->only('name', 'password');

        // Check if credentials are valid
        if (!$token = auth()->guard('api')->attempt($credentials)) {
            // Return login failed response
            return response()->json([
                'success' => false,
                'message' => 'Username or Password is incorrect'
            ], 400);
        }

        $roles = auth()->guard('api')->user()->roles->pluck('name')->implode(',');
        // Return success response with token and user details
        return response()->json([
            'success'       => true,
            'user'          => auth()->guard('api')->user()->only(['name']),
            'roles'         => $roles,
            'permissions'   => auth()->guard('api')->user()->getAllPermissions()->pluck('name'),
            'token'         => $token
        ], 200);
    }

    /**
     * Handle user logout.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        // Invalidate JWT token
        JWTAuth::invalidate(JWTAuth::getToken());

        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ], 200);
    }
}
