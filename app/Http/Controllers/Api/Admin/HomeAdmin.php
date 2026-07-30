<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Obat;
use App\Models\User;
use App\Models\Pakan;
use App\Models\Jadwal;
use App\Models\Vaksin;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HomeAdmin extends Controller
{
    public function getDashboardAdmin(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }
            $adminData = [
                'id' => $user->id,
                'name' => $user->name,
            ];
            $jumlahKaryawan = User::where('is_admin', false)->count();
            $jumlahPakan = Pakan::count();
            $jumlahObat = Obat::count();
            $jumlahVaksin = Vaksin::count();
            $jumlahJadwal = Jadwal::count();
            $notifikasiHariIni = Notification::where('user_id', $user->id)
                ->whereDate('created_at', today())
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(['id', 'title', 'body', 'type', 'data_id', 'is_read', 'created_at']);

            return response()->json([
                'success' => true,
                'message' => 'Dashboard data berhasil diambil',
                'data' => [
                    'admin' => $adminData,
                    'jumlah_karyawan' => $jumlahKaryawan,
                    'jumlah_pakan' => $jumlahPakan,
                    'jumlah_obat' => $jumlahObat,
                    'jumlah_vaksin' => $jumlahVaksin,
                    'jumlah_jadwal' => $jumlahJadwal,
                    'notifikasi_hari_ini' => $notifikasiHariIni,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
