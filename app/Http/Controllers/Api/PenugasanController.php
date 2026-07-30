<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Penugasan;
use App\Models\UserDevice;
use App\Models\Notification;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Database\Eloquent\Builder;

class PenugasanController extends Controller
{
     protected $fcmService;
      public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
     public function getKaryawanForPenugasan(Request $request)
    {
        try {
            $query = User::where('is_admin', 0)
                ->with('userDetail') 
                ->orderBy('name');
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function (Builder $q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhereHas('userDetail', function (Builder $subQ) use ($search) {
                          $subQ->where('alamat', 'like', "%{$search}%");
                      });
                });
            }
            if ($request->has('paginate') && $request->paginate) {
                $perPage = $request->per_page ?? 10;
                $karyawan = $query->paginate($perPage);
            } else {
                $karyawan = $query->get();
            }
            $karyawan->each(function ($user) {
                $user->alamat = $user->userDetail ? $user->userDetail->alamat : '';
                $user->kontak = $user->userDetail ? $user->userDetail->kontak : '';
                $user->tgl_aktif = $user->userDetail ? $user->userDetail->tgl_aktif : null;
                unset($user->userDetail);
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Daftar karyawan berhasil diambil',
                'data' => $karyawan
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar karyawan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
   public function storePenugasan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'jenis_penugasan' => 'required|in:pakan,vaksin,obat,pemeliharaan_ayam',
            'karyawan_ids' => 'required|array',
            'karyawan_ids.*' => 'exists:users,id',
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'admin_device_token' => 'nullable|string',
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
            $waktu = Carbon::parse($request->waktu);
            if ($request->filled('admin_device_token')) {
                UserDevice::updateOrCreate(
                    ['device_token' => $request->admin_device_token],
                    [
                        'user_id' => auth()->id(),
                        'device_type' => $request->device_type ?? 'unknown',
                        'device_name' => $request->device_name ?? 'Unknown Device',
                        'is_active' => true,
                        'last_active_at' => now(),
                    ]
                );
            }         
            $penugasanList = [];
            $karyawanIds = $request->karyawan_ids;
            $jenisPenugasanLabels = [
                'pakan' => 'Pemberian Pakan',
                'vaksin' => 'Vaksinasi',
                'obat' => 'Pemberian Obat',
                'pemeliharaan_ayam' => 'Pemeliharaan Ayam'
            ];
            $jenisPenugasanLabel = $jenisPenugasanLabels[$request->jenis_penugasan] ?? $request->jenis_penugasan;
            $tanggalFormatted = Carbon::parse($request->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY');
            $waktuFormatted = $waktu->format('H:i');
            foreach ($karyawanIds as $karyawanId) {
                $penugasan = Penugasan::create([
                    'judul' => $request->judul,
                    'deskripsi' => $request->deskripsi,
                    'jenis_penugasan' => $request->jenis_penugasan,
                    'karyawan_id' => $karyawanId,
                    'admin_id' => auth()->id(),
                    'tanggal' => $request->tanggal,
                    'waktu' => $waktu->format('H:i:s'),
                    'status' => 'pending',
                ]);
                
                $penugasanList[] = $penugasan;
                $notificationTitle = "$jenisPenugasanLabel";
                $notificationBody = "{$request->judul}\n" .
                                   "Jadwal: {$tanggalFormatted}, {$waktuFormatted}";
                if (!empty($request->deskripsi)) {
                    $shortDesc = strlen($request->deskripsi) > 100 
                        ? substr($request->deskripsi, 0, 100) . '...' 
                        : $request->deskripsi;
                    $notificationBody .= "\n\n{$shortDesc}";
                }
                
                $notification = Notification::create([
                    'user_id' => $karyawanId,
                    'title' => $notificationTitle,
                    'body' => $notificationBody,
                    'type' => 'penugasan',
                    'data_id' => $penugasan->id,
                    'is_read' => false,
                    'is_sent' => false,
                ]);
            }
            
            $this->sendNotificationsToKaryawan($karyawanIds, $penugasanList[0], $notificationTitle, $notificationBody);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Penugasan berhasil dibuat',
                'data' => $penugasanList
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat penugasan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Karyawan menandai penugasannya sebagai selesai (1 langkah) + notifikasi ke admin.
     */
    public function selesaikanPenugasan($id)
    {
        try {
            $user = auth()->user();

            $penugasan = Penugasan::find($id);

            if (!$penugasan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penugasan tidak ditemukan'
                ], 404);
            }

            // Hanya karyawan pemilik tugas yang boleh menyelesaikan
            if ((int) $penugasan->karyawan_id !== (int) $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak berhak mengubah tugas ini'
                ], 403);
            }

            if ($penugasan->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas ini sudah selesai'
                ], 422);
            }

            $penugasan->update(['status' => 'completed']);

            $this->notifyAdminTaskCompleted($penugasan, $user);

            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil ditandai selesai',
                'data' => $penugasan->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan tugas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function notifyAdminTaskCompleted(Penugasan $penugasan, User $karyawan)
    {
        try {
            $jenisLabels = [
                'pakan' => 'Pemberian Pakan',
                'vaksin' => 'Vaksinasi',
                'obat' => 'Pemberian Obat',
                'pemeliharaan_ayam' => 'Pemeliharaan Ayam',
            ];
            $jenisLabel = $jenisLabels[$penugasan->jenis_penugasan] ?? $penugasan->jenis_penugasan;

            $title = 'Tugas Selesai';
            $body = "{$karyawan->name} telah menyelesaikan tugas: {$penugasan->judul} ({$jenisLabel}).";

            $adminId = $penugasan->admin_id;

            Notification::create([
                'user_id' => $adminId,
                'title' => $title,
                'body' => $body,
                'type' => 'penugasan_selesai',
                'data_id' => $penugasan->id,
                'is_read' => false,
                'is_sent' => false,
            ]);

            $data = [
                'type' => 'penugasan_selesai',
                'id' => (string) $penugasan->id,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ];

            $deviceTokens = UserDevice::where('user_id', $adminId)
                ->where('is_active', true)
                ->pluck('device_token')
                ->toArray();

            if (!empty($deviceTokens)) {
                $this->fcmService->sendMultiNotification($deviceTokens, $title, $body, $data);

                Notification::where('type', 'penugasan_selesai')
                    ->where('user_id', $adminId)
                    ->where('data_id', $penugasan->id)
                    ->update(['is_sent' => true, 'sent_at' => now()]);
            }
        } catch (\Exception $e) {
            \Log::error('Gagal mengirim notifikasi tugas selesai: ' . $e->getMessage());
        }
    }

    private function sendNotificationsToKaryawan(array $karyawanIds, Penugasan $penugasan, string $title, string $body)
    {
        try {

            $data = [
                'type' => 'penugasan',
                'id' => (string) $penugasan->id,
                'jenis' => $penugasan->jenis_penugasan,
                'tanggal' => $penugasan->tanggal,
                'waktu' => $penugasan->waktu,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ];
            
            $deviceTokens = UserDevice::whereIn('user_id', $karyawanIds)
                ->where('is_active', true)
                ->pluck('device_token')
                ->toArray();
            
            if (!empty($deviceTokens)) {
                $this->fcmService->sendMultiNotification($deviceTokens, $title, $body, $data);
                
                Notification::where('type', 'penugasan')
                    ->whereIn('user_id', $karyawanIds)
                    ->where('data_id', $penugasan->id)
                    ->update([
                        'is_sent' => true,
                        'sent_at' => now(),
                    ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error sending notifications: ' . $e->getMessage());
        }
    }
}
