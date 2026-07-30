<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'device_token' => 'nullable|string',
            'device_type' => 'nullable|string',
            'device_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau password salah'
            ], 401);
        }

        $abilities = $user->is_admin ? ['admin'] : ['user'];
        $token = $user->createToken('auth_token', $abilities)->plainTextToken;

        $userDetail = null;
        if (!$user->is_admin) {
            $userDetail = UserDetail::where('user_id', $user->id)->first();
        }

        if ($request->filled('device_token')) {
            UserDevice::updateOrCreate(
                ['device_token' => $request->device_token],
                [
                    'user_id' => $user->id,
                    'device_type' => $request->device_type ?? 'unknown',
                    'device_name' => $request->device_name ?? 'Unknown Device',
                    'is_active' => true,
                    'last_active_at' => now(),
                ]
            );
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'user' => $user,
            'user_detail' => $userDetail,
            'role' => $user->is_admin ? 'admin' : 'user',
            'token' => $token
        ]);
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'kontak' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:255',
            'tgl_aktif' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if ($request->kontak || $request->alamat || $request->tgl_aktif) {
            UserDetail::create([
                'user_id' => $user->id,
                'kontak' => $request->kontak,
                'alamat' => $request->alamat,
                'tgl_aktif' => $request->tgl_aktif
            ]);
        }

        $token = $user->createToken('auth_token', ['user'])->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function updateFcmToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_token' => 'required|string',
            'device_type' => 'nullable|string',
            'device_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        UserDevice::updateOrCreate(
            ['device_token' => $request->device_token],
            [
                'user_id' => $user->id,
                'device_type' => $request->device_type ?? 'unknown',
                'device_name' => $request->device_name ?? 'Unknown Device',
                'is_active' => true,
                'last_active_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'FCM token berhasil diperbarui',
        ]);
    }

    public function logout(Request $request)
    {
        $user = auth()->user();
        if ($request->filled('device_token')) {
            UserDevice::where('user_id', $user->id)
                ->where('device_token', $request->device_token)
                ->update(['is_active' => false]);
        }
        $user->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ]);
    }

}
