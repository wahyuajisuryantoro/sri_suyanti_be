<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PeriodeController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $periodes = Periode::orderBy('tgl_mulai', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data periode berhasil diambil',
                'data' => $periodes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data periode',
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

            $validator = Validator::make($request->all(), [
                'tgl_mulai' => 'required|date|after_or_equal:today',
                'tgl_selesai' => 'required|date|after:tgl_mulai',
                'total_ayam' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $overlappingPeriode = Periode::where('status', true)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('tgl_mulai', [$request->tgl_mulai, $request->tgl_selesai])
                        ->orWhereBetween('tgl_selesai', [$request->tgl_mulai, $request->tgl_selesai])
                        ->orWhere(function ($subQuery) use ($request) {
                            $subQuery->where('tgl_mulai', '<=', $request->tgl_mulai)
                                ->where('tgl_selesai', '>=', $request->tgl_selesai);
                        });
                })
                ->first();

            if ($overlappingPeriode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode ini bertumpuk dengan periode yang sudah ada',
                    'overlapping_period' => $overlappingPeriode
                ], 422);
            }

            $periode = Periode::create([
                'tgl_mulai' => $request->tgl_mulai,
                'tgl_selesai' => $request->tgl_selesai,
                'total_ayam' => $request->total_ayam,
                'status' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Periode berhasil dibuat',
                'data' => $periode
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create periode',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $periode = Periode::with(['jadwal.jadwalPakan.pakan', 'jadwal.jadwalObat.obat', 'jadwal.jadwalVaksin.vaksin'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Data periode berhasil diambil',
                'data' => $periode
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Periode tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $periode = Periode::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'tgl_mulai' => 'required|date',
                'tgl_selesai' => 'required|date|after:tgl_mulai',
                'total_ayam' => 'required|integer|min:1',
                'status' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $overlappingPeriode = Periode::where('status', true)
                ->where('id', '!=', $id)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('tgl_mulai', [$request->tgl_mulai, $request->tgl_selesai])
                        ->orWhereBetween('tgl_selesai', [$request->tgl_mulai, $request->tgl_selesai])
                        ->orWhere(function ($subQuery) use ($request) {
                            $subQuery->where('tgl_mulai', '<=', $request->tgl_mulai)
                                ->where('tgl_selesai', '>=', $request->tgl_selesai);
                        });
                })
                ->first();

            if ($overlappingPeriode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode ini bertumpuk dengan periode yang sudah ada',
                    'overlapping_period' => $overlappingPeriode
                ], 422);
            }

            $periode->update([
                'tgl_mulai' => $request->tgl_mulai,
                'tgl_selesai' => $request->tgl_selesai,
                'total_ayam' => $request->total_ayam,
                'status' => $request->status ?? $periode->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Periode berhasil diupdate',
                'data' => $periode
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update periode',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $periode = Periode::findOrFail($id);

            if ($periode->jadwal()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus periode yang sudah memiliki jadwal'
                ], 422);
            }

            $periode->delete();

            return response()->json([
                'success' => true,
                'message' => 'Periode berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete periode',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getActivePeriodes(): JsonResponse
    {
        try {
            $periodes = Periode::where('status', true)
                ->orderBy('tgl_mulai', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Data periode aktif berhasil diambil',
                'data' => $periodes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data periode aktif',
                'error' => $e->getMessage()
            ], 500);
        }
    }

     public function getCurrentPeriode(): JsonResponse
    {
        try {
            $currentPeriode = Periode::where('status', true)
                ->where('tgl_mulai', '<=', today())
                ->where('tgl_selesai', '>=', today())
                ->first();

            if (!$currentPeriode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada periode yang sedang berjalan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Periode saat ini berhasil diambil',
                'data' => $currentPeriode
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil periode saat ini',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
