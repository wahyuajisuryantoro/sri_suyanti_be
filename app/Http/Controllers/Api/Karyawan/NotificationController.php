<?php

namespace App\Http\Controllers\Api\Karyawan;

use App\Models\Catatan;
use App\Models\Penugasan;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Detail satu notifikasi + entitas terkait (penugasan / catatan stok).
     * Sekaligus menandai notifikasi sebagai sudah dibaca.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $notification = Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            if (!$notification->is_read) {
                $notification->update(['is_read' => true]);
            }

            $related = null;
            switch ($notification->type) {
                case 'penugasan':
                case 'penugasan_selesai':
                    $penugasan = Penugasan::with('karyawan:id,name')->find($notification->data_id);
                    if ($penugasan) {
                        $related = [
                            'kind' => 'penugasan',
                            'judul' => $penugasan->judul,
                            'deskripsi' => $penugasan->deskripsi,
                            'jenis_penugasan' => $penugasan->jenis_penugasan,
                            'tanggal' => $penugasan->tanggal,
                            'waktu' => $penugasan->waktu,
                            'status' => $penugasan->status,
                            'karyawan' => optional($penugasan->karyawan)->name,
                        ];
                    }
                    break;
                case 'stock_update':
                    $catatan = Catatan::with('user:id,name')->find($notification->data_id);
                    if ($catatan) {
                        $related = [
                            'kind' => 'catatan',
                            'jenis_item' => $catatan->jenis_item,
                            'stok_sebelum' => $catatan->stok_sebelum,
                            'stok_sesudah' => $catatan->stok_sesudah,
                            'jumlah_perubahan' => $catatan->jumlah_perubahan,
                            'jenis_perubahan' => $catatan->jenis_perubahan,
                            'catatan' => $catatan->catatan,
                            'karyawan' => optional($catatan->user)->name,
                            'tanggal_perubahan' => $catatan->tanggal_perubahan,
                        ];
                    }
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail notifikasi berhasil diambil',
                'data' => [
                    'notification' => $notification->fresh(),
                    'related' => $related,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get notification detail',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllNotifications(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $notifications = Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Notifications retrieved successfully',
                'data' => $notifications
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function readNotification(int $id): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $notification = Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->update([
                'is_read' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read successfully',
                'data' => $notification->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function readAllNotifications(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notifications as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteNotification(int $id): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $notification = Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteAllNotifications(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            Notification::where('user_id', $user->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'All notifications deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
