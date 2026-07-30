<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use App\Models\Catatan;
use App\Models\UserDetail;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ManajemenKaryawan extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $karyawan = User::where('is_admin', false)
                ->with(['userDetail'])
                ->select('id', 'name', 'created_at', 'updated_at')
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedData = $karyawan->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'kontak' => $user->userDetail->kontak ?? null,
                    'alamat' => $user->userDetail->alamat ?? null,
                    'tgl_aktif' => $user->userDetail->tgl_aktif ?? null,
                    'gambar' => $user->userDetail->gambar ?? null,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Data karyawan berhasil diambil',
                'data' => $formattedData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get employee data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $karyawan = User::where('id', $id)
                ->where('is_admin', false)
                ->with(['userDetail'])
                ->select('id', 'name', 'created_at', 'updated_at')
                ->first();

            if (!$karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Karyawan tidak ditemukan'
                ], 404);
            }

            $formattedData = [
                'id' => $karyawan->id,
                'name' => $karyawan->name,
                'kontak' => $karyawan->userDetail->kontak ?? null,
                'alamat' => $karyawan->userDetail->alamat ?? null,
                'tgl_aktif' => $karyawan->userDetail->tgl_aktif ?? null,
                'gambar' => $karyawan->userDetail->gambar ?? null,
                'created_at' => $karyawan->created_at,
                'updated_at' => $karyawan->updated_at,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Detail karyawan berhasil diambil',
                'data' => $formattedData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get employee detail',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Validasi input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'kontak' => 'nullable|string|max:20',
                'alamat' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Buat user baru
                $karyawan = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'is_admin' => false,
                ]);

                // Buat user detail jika ada kontak atau alamat
                if ($request->kontak || $request->alamat) {
                    UserDetail::create([
                        'user_id' => $karyawan->id,
                        'kontak' => $request->kontak,
                        'alamat' => $request->alamat ?? '',
                        'tgl_aktif' => now()->toDateString(),
                    ]);
                }

                DB::commit();

                // Load relasi
                $karyawan->load('userDetail');

                return response()->json([
                    'success' => true,
                    'message' => 'Karyawan berhasil ditambahkan',
                    'data' => [
                        'id' => $karyawan->id,
                        'name' => $karyawan->name,
                        'email' => $karyawan->email,
                        'kontak' => $karyawan->userDetail?->kontak,
                        'alamat' => $karyawan->userDetail?->alamat,
                        'created_at' => $karyawan->created_at,
                    ]
                ], 201);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan karyawan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            return DB::transaction(function () use ($id) {
                $karyawan = User::where('id', $id)
                    ->where('is_admin', false)
                    ->first();

                if (!$karyawan) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Karyawan tidak ditemukan'
                    ], 404);
                }
                $hasActiveData = $this->checkActiveData($id);

                if ($hasActiveData['has_data']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak dapat menghapus karyawan karena masih memiliki data aktif',
                        'active_data' => $hasActiveData['data']
                    ], 422);
                }
                $karyawan->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Karyawan berhasil dihapus'
                ], 200);
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function checkActiveData($userId): array
    {
        $activeData = [];
        $hasData = false;
        $unreadNotifications = Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        if ($unreadNotifications > 0) {
            $activeData[] = "Notifikasi belum dibaca: {$unreadNotifications}";
            $hasData = true;
        }
        $recentCatatan = Catatan::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        if ($recentCatatan > 0) {
            $activeData[] = "Catatan dalam 30 hari terakhir: {$recentCatatan}";
            $hasData = true;
        }

        return [
            'has_data' => $hasData,
            'data' => $activeData
        ];
    }
}
