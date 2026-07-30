<?php

namespace App\Http\Controllers\Api\Karyawan;

use App\Models\Penugasan;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HomeKaryawanController extends Controller
{
    public function getDataKaryawan(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->is_admin ? 'admin' : 'karyawan'
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data karyawan retrieved successfully',
                'data' => $userData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

     public function getStatusPenugasan(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $statusCounts = Penugasan::where('karyawan_id', $user->id)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->get()
                ->pluck('total', 'status')
                ->toArray();

            $allStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];
            $formattedCounts = [];
            
            foreach ($allStatuses as $status) {
                $formattedCounts[$status] = $statusCounts[$status] ?? 0;
            }

            $totalPenugasan = array_sum($formattedCounts);

            return response()->json([
                'success' => true,
                'message' => 'Status penugasan retrieved successfully',
                'data' => [
                    'status_counts' => $formattedCounts,
                    'total_penugasan' => $totalPenugasan
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve status penugasan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

     public function getTugasHariIni(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $today = Carbon::today()->format('Y-m-d');

            $tugasHariIni = Penugasan::where('karyawan_id', $user->id)
                ->where('tanggal', $today)
                ->whereIn('status', ['pending', 'in_progress'])
                ->orderBy('waktu', 'asc')
                ->get()
                ->map(function ($tugas) {
                    return [
                        'id' => $tugas->id,
                        'judul' => $tugas->judul,
                        'deskripsi' => $tugas->deskripsi,
                        'jenis_penugasan' => $tugas->jenis_penugasan,
                        'tanggal' => $tugas->tanggal,
                        'waktu' => $tugas->waktu,
                        'status' => $tugas->status,
                        'created_at' => $tugas->created_at,
                        'updated_at' => $tugas->updated_at
                    ];
                });

            $totalTugasHariIni = $tugasHariIni->count();

            return response()->json([
                'success' => true,
                'message' => 'Tugas hari ini retrieved successfully',
                'data' => [
                    'tugas_hari_ini' => $tugasHariIni,
                    'total_tugas' => $totalTugasHariIni,
                    'tanggal' => $today
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tugas hari ini',
                'error' => $e->getMessage()
            ], 500);
        }
    }
     public function getDashboardSummary(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $today = Carbon::today()->format('Y-m-d');

            // Get user data
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->is_admin ? 'admin' : 'karyawan'
            ];

            // Get status counts
            $statusCounts = Penugasan::where('karyawan_id', $user->id)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->get()
                ->pluck('total', 'status')
                ->toArray();

            $allStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];
            $formattedCounts = [];
            
            foreach ($allStatuses as $status) {
                $formattedCounts[$status] = $statusCounts[$status] ?? 0;
            }

            // Get today's tasks
            $tugasHariIni = Penugasan::where('karyawan_id', $user->id)
                ->where('tanggal', $today)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();

            // Get unread notifications count
            $unreadNotifications = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Dashboard summary retrieved successfully',
                'data' => [
                    'user' => $userData,
                    'status_penugasan' => $formattedCounts,
                    'total_penugasan' => array_sum($formattedCounts),
                    'tugas_hari_ini' => $tugasHariIni,
                    'unread_notifications' => $unreadNotifications,
                    'tanggal' => $today
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getRecentTasks(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $sevenDaysAgo = Carbon::now()->subDays(7)->format('Y-m-d');
            $today = Carbon::today()->format('Y-m-d');

            $recentTasks = Penugasan::where('karyawan_id', $user->id)
                ->whereBetween('tanggal', [$sevenDaysAgo, $today])
                ->orderBy('tanggal', 'desc')
                ->orderBy('waktu', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($tugas) {
                    return [
                        'id' => $tugas->id,
                        'judul' => $tugas->judul,
                        'deskripsi' => $tugas->deskripsi,
                        'jenis_penugasan' => $tugas->jenis_penugasan,
                        'tanggal' => $tugas->tanggal,
                        'waktu' => $tugas->waktu,
                        'status' => $tugas->status,
                        'created_at' => $tugas->created_at
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Recent tasks retrieved successfully',
                'data' => [
                    'recent_tasks' => $recentTasks,
                    'total_recent_tasks' => $recentTasks->count()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve recent tasks',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
