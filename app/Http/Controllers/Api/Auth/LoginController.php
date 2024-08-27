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
        // Validasi input pengguna
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'password' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        // Coba autentikasi pengguna
        if (!$token = auth()->guard('api')->attempt($request->only('name', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Username or Password is incorrect'
            ], 400);
        }
    
        // Dapatkan data pengguna yang login
        $user = auth()->guard('api')->user()->load('students');
    
        // Susun respon sukses
        return response()->json([
            'success'       => true,
            'user'          => [
                'id'            => $user->id,
                'name'          => $user->name,
                'industri_id'   => optional($user->student)->industri_id, 
                'roles'         => $user->roles->pluck('name')->implode(','),// Alternatif untuk menangani null dengan lebih ringkas
            ],
            
            'permissions'   => $user->getAllPermissions()->pluck('name'),
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