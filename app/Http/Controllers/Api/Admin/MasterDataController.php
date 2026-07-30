<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Obat;
use App\Models\Pakan;
use App\Models\Vaksin;
use App\Models\Catatan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MasterDataController extends Controller
{
    public function getPakan(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $pakan = Pakan::all();

            return response()->json([
                'success' => true,
                'message' => 'Data pakan berhasil diambil',
                'data' => $pakan
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get pakan data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getObat(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $obat = Obat::all();

            return response()->json([
                'success' => true,
                'message' => 'Data obat berhasil diambil',
                'data' => $obat
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get obat data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getVaksin(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $vaksin = Vaksin::all();

            return response()->json([
                'success' => true,
                'message' => 'Data vaksin berhasil diambil',
                'data' => $vaksin
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get vaksin data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function storePakan(Request $request): JsonResponse
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
                'jenis_pakan' => 'required|string|max:255',
                'nama_pakan' => 'required|string|max:255',
                'satuan' => 'required|string|max:50',
                'stok' => 'nullable|integer|min:0',
                'deskripsi_pakan' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pakan = Pakan::create([
                'jenis_pakan' => $request->jenis_pakan,
                'nama_pakan' => $request->nama_pakan,
                'stok' => $request->stok ?? 0,
                'satuan' => $request->satuan,
                'deskripsi_pakan' => $request->deskripsi_pakan ?? '',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data pakan berhasil disimpan',
                'data' => $pakan
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to store pakan data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeObat(Request $request): JsonResponse
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
                'jenis_obat' => 'required|string|max:255',
                'nama_obat' => 'required|string|max:255',
                'satuan' => 'required|string|max:50',
                'stok' => 'nullable|integer|min:0',
                'deskripsi_obat' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $obat = Obat::create([
                'jenis_obat' => $request->jenis_obat,
                'nama_obat' => $request->nama_obat,
                'stok' => $request->stok ?? 0,
                'satuan' => $request->satuan,
                'deskripsi_obat' => $request->deskripsi_obat ?? '',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data obat berhasil disimpan',
                'data' => $obat
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to store obat data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeVaksin(Request $request): JsonResponse
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
                'jenis_vaksin' => 'required|string|max:255',
                'nama_vaksin' => 'required|string|max:255',
                'satuan' => 'required|string|max:50',
                'stok' => 'nullable|integer|min:0',
                'deskripsi_vaksin' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $vaksin = Vaksin::create([
                'jenis_vaksin' => $request->jenis_vaksin,
                'nama_vaksin' => $request->nama_vaksin,
                'stok' => $request->stok ?? 0,
                'satuan' => $request->satuan,
                'deskripsi_vaksin' => $request->deskripsi_vaksin ?? '',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data vaksin berhasil disimpan',
                'data' => $vaksin
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to store vaksin data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restock / penyesuaian stok oleh admin (set nilai absolut).
     * Dipakai untuk aksi "Tambah" (current + jumlah) maupun "Sesuaikan" (set langsung),
     * keduanya mengirim nilai akhir sebagai stok_baru.
     */
    public function updateStok(Request $request): JsonResponse
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
                'jenis_item' => 'required|in:pakan,obat,vaksin',
                'item_id' => 'required|integer',
                'stok_baru' => 'required|integer|min:0',
                'catatan' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            return DB::transaction(function () use ($request, $user) {
                switch ($request->jenis_item) {
                    case 'pakan':
                        $item = Pakan::findOrFail($request->item_id);
                        $itemName = $item->nama_pakan;
                        break;
                    case 'obat':
                        $item = Obat::findOrFail($request->item_id);
                        $itemName = $item->nama_obat;
                        break;
                    case 'vaksin':
                        $item = Vaksin::findOrFail($request->item_id);
                        $itemName = $item->nama_vaksin;
                        break;
                }

                $stokLama = (int) $item->stok;
                $stokBaru = (int) $request->stok_baru;

                if ($stokBaru === $stokLama) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok tidak berubah dari nilai sebelumnya.',
                    ], 422);
                }

                $item->update(['stok' => $stokBaru]);

                // Catat untuk audit (tanpa notifikasi; perubahan dilakukan admin sendiri)
                Catatan::create([
                    'user_id' => $user->id,
                    'jenis_item' => $request->jenis_item,
                    'item_id' => $item->id,
                    'stok_sebelum' => $stokLama,
                    'stok_sesudah' => $stokBaru,
                    'jumlah_perubahan' => abs($stokBaru - $stokLama),
                    'jenis_perubahan' => $stokBaru > $stokLama ? 'penambahan' : 'pengurangan',
                    'catatan' => $request->catatan ?: 'Penyesuaian stok oleh admin',
                    'status' => 'disetujui',
                    'tanggal_perubahan' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Stok {$itemName} berhasil diperbarui",
                    'data' => $item->fresh(),
                ], 200);
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
