<?php

namespace App\Http\Controllers\Api\Karyawan;

use App\Models\Obat;
use App\Models\Pakan;
use App\Models\Jadwal;
use App\Models\Vaksin;
use App\Models\Catatan;
use App\Models\UserDevice;
use App\Helpers\DateHelper;
use App\Models\Notification;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MasterDataKaryawanController extends Controller
{
    protected $fcmService;

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
    public function getPakan(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || $user->is_admin) {
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

            if (!$user || $user->is_admin) {
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

            if (!$user || $user->is_admin) {
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

    public function getJadwalHariIni(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || $user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $today = now()->format('Y-m-d');

            $notifications = Notification::where('user_id', $user->id)
                ->where('type', 'jadwal_pemeliharaan')
                ->whereDate('created_at', $today)
                ->get();

            $jadwalData = [];

            foreach ($notifications as $notif) {
                $jadwal = Jadwal::with([
                    'jadwalPakan.pakan',
                    'jadwalObat.obat',
                    'jadwalVaksin.vaksin'
                ])->find($notif->data_id);

                if ($jadwal) {
                    $items = [];

                    foreach ($jadwal->jadwalPakan as $jp) {
                        $items[] = [
                            'type' => 'pakan',
                            'id' => $jp->pakan->id,
                            'nama' => $jp->pakan->nama_pakan,
                            'jumlah_digunakan' => $jp->jumlah_pakan,
                            'stok_saat_ini' => $jp->pakan->stok,
                            'satuan' => $jp->pakan->satuan,
                            'waktu' => DateHelper::formatWaktuIndonesia($jp->waktu), // GUNAKAN HELPER
                            'reference_id' => $jp->id
                        ];
                    }

                    foreach ($jadwal->jadwalObat as $jo) {
                        $items[] = [
                            'type' => 'obat',
                            'id' => $jo->obat->id,
                            'nama' => $jo->obat->nama_obat,
                            'jumlah_digunakan' => $jo->jumlah_obat,
                            'stok_saat_ini' => $jo->obat->stok,
                            'satuan' => $jo->obat->satuan,
                            'waktu' => DateHelper::formatWaktuIndonesia($jo->waktu), // GUNAKAN HELPER
                            'reference_id' => $jo->id
                        ];
                    }

                    foreach ($jadwal->jadwalVaksin as $jv) {
                        $items[] = [
                            'type' => 'vaksin',
                            'id' => $jv->vaksin->id,
                            'nama' => $jv->vaksin->nama_vaksin,
                            'jumlah_digunakan' => $jv->jumlah_vaksin,
                            'stok_saat_ini' => $jv->vaksin->stok,
                            'satuan' => $jv->vaksin->satuan,
                            'waktu' => DateHelper::formatWaktuIndonesia($jv->waktu), // GUNAKAN HELPER
                            'reference_id' => $jv->id
                        ];
                    }

                    $jadwalData[] = [
                        'jadwal_id' => $jadwal->id,
                        'tanggal' => DateHelper::formatTanggalIndonesia($jadwal->tgl_penjadwalan), // GUNAKAN HELPER
                        'items' => $items,
                        'notification_id' => $notif->id
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Jadwal hari ini berhasil diambil',
                'data' => $jadwalData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get today schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateStokFromJadwal(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || $user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'jadwal_reference_id' => 'required|integer',
                'jenis_item' => 'required|in:pakan,obat,vaksin',
                'item_id' => 'required|integer',
                'jumlah_digunakan' => 'required|integer|min:1',
                'catatan' => 'required|string|max:500',
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

                $stokLama = $item->stok;

                // Tolak bila stok tidak mencukupi (jangan diam-diam dipaksa ke 0)
                if ($request->jumlah_digunakan > $stokLama) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stok {$itemName} tidak mencukupi. Stok saat ini: {$stokLama} {$item->satuan}, diminta: {$request->jumlah_digunakan} {$item->satuan}.",
                        'data' => [
                            'stok_saat_ini' => $stokLama,
                            'jumlah_diminta' => $request->jumlah_digunakan,
                        ],
                    ], 422);
                }

                $stokBaru = $stokLama - $request->jumlah_digunakan;

                $item->update(['stok' => $stokBaru]);

                // Simpan catatan pengurangan
                $catatan = Catatan::create([
                    'user_id' => $user->id,
                    'jenis_item' => $request->jenis_item,
                    'item_id' => $request->item_id,
                    'stok_sebelum' => $stokLama,
                    'stok_sesudah' => $stokBaru,
                    'jumlah_perubahan' => $request->jumlah_digunakan,
                    'jenis_perubahan' => 'pengurangan',
                    'catatan' => $request->catatan,
                    'tanggal_perubahan' => now(),
                ]);

                $this->sendNotificationToAdmin($catatan, $itemName);

                return response()->json([
                    'success' => true,
                    'message' => "Stok {$itemName} berhasil diperbarui sesuai jadwal",
                    'data' => [
                        'item' => $item->fresh(),
                        'catatan' => $catatan,
                    ]
                ], 200);
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock from schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStokPakan(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || $user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'pakan_id' => 'required|exists:pakan,id',
                'stok_baru' => 'required|integer|min:0',
                'catatan' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pakan = Pakan::findOrFail($request->pakan_id);
            $stokLama = $pakan->stok;

            $pakan->update(['stok' => $request->stok_baru]);

            // Simpan catatan
            $catatan = Catatan::create([
                'user_id' => $user->id,
                'jenis_item' => 'pakan',
                'item_id' => $pakan->id,
                'stok_sebelum' => $stokLama,
                'stok_sesudah' => $request->stok_baru,
                'jumlah_perubahan' => abs($request->stok_baru - $stokLama),
                'jenis_perubahan' => $request->stok_baru > $stokLama ? 'penambahan' : 'pengurangan',
                'catatan' => $request->catatan,
                'tanggal_perubahan' => now(),
            ]);

            // Kirim notifikasi ke admin
            $this->sendNotificationToAdmin($catatan, $pakan->nama_pakan);

            return response()->json([
                'success' => true,
                'message' => 'Stok pakan berhasil diperbarui',
                'data' => $pakan->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update pakan stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStokObat(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || $user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'obat_id' => 'required|exists:obat,id',
                'stok_baru' => 'required|integer|min:0',
                'catatan' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $obat = Obat::findOrFail($request->obat_id);
            $stokLama = $obat->stok;

            $obat->update(['stok' => $request->stok_baru]);

            // Simpan catatan
            $catatan = Catatan::create([
                'user_id' => $user->id,
                'jenis_item' => 'obat',
                'item_id' => $obat->id,
                'stok_sebelum' => $stokLama,
                'stok_sesudah' => $request->stok_baru,
                'jumlah_perubahan' => abs($request->stok_baru - $stokLama),
                'jenis_perubahan' => $request->stok_baru > $stokLama ? 'penambahan' : 'pengurangan',
                'catatan' => $request->catatan,
                'tanggal_perubahan' => now(),
            ]);

            // Kirim notifikasi ke admin
            $this->sendNotificationToAdmin($catatan, $obat->nama_obat);

            return response()->json([
                'success' => true,
                'message' => 'Stok obat berhasil diperbarui',
                'data' => $obat->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update obat stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStokVaksin(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user || $user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'vaksin_id' => 'required|exists:vaksin,id',
                'stok_baru' => 'required|integer|min:0',
                'catatan' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $vaksin = Vaksin::findOrFail($request->vaksin_id);
            $stokLama = $vaksin->stok;

            $vaksin->update(['stok' => $request->stok_baru]);

            // Simpan catatan
            $catatan = Catatan::create([
                'user_id' => $user->id,
                'jenis_item' => 'vaksin',
                'item_id' => $vaksin->id,
                'stok_sebelum' => $stokLama,
                'stok_sesudah' => $request->stok_baru,
                'jumlah_perubahan' => abs($request->stok_baru - $stokLama),
                'jenis_perubahan' => $request->stok_baru > $stokLama ? 'penambahan' : 'pengurangan',
                'catatan' => $request->catatan,
                'tanggal_perubahan' => now(),
            ]);

            // Kirim notifikasi ke admin
            $this->sendNotificationToAdmin($catatan, $vaksin->nama_vaksin);

            return response()->json([
                'success' => true,
                'message' => 'Stok vaksin berhasil diperbarui',
                'data' => $vaksin->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update vaksin stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    private function sendNotificationToAdmin($catatan, $itemName)
    {
        try {
            $jenisCatatan = ucfirst($catatan->jenis_item);
            $jenisPerubahan = ucfirst($catatan->jenis_perubahan);

            $title = "Update Stok {$jenisCatatan}";
            $body = "{$jenisPerubahan} stok {$itemName}\n";
            $body .= "Dari: {$catatan->stok_sebelum} → {$catatan->stok_sesudah}\n";
            $body .= "Catatan: {$catatan->catatan}\n";
            $body .= "Oleh: {$catatan->user->name}";

            // Data untuk FCM
            $data = [
                'type' => 'stock_update',
                'catatan_id' => (string) $catatan->id,
                'jenis_item' => $catatan->jenis_item,
                'item_name' => $itemName,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ];

            // Kirim notifikasi ke semua admin
            $adminUsers = \App\Models\User::where('is_admin', true)->get();

            foreach ($adminUsers as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => $title,
                    'body' => $body,
                    'type' => 'stock_update',
                    'data_id' => $catatan->id,
                    'is_read' => false,
                    'is_sent' => false,
                ]);
            }

            // Kirim FCM ke device admin
            $adminIds = $adminUsers->pluck('id')->toArray();
            $deviceTokens = UserDevice::whereIn('user_id', $adminIds)
                ->where('is_active', true)
                ->pluck('device_token')
                ->toArray();

            if (!empty($deviceTokens)) {
                $fcmResult = $this->fcmService->sendMultiNotification($deviceTokens, $title, $body, $data);

                // Update status notifikasi sebagai terkirim
                Notification::where('type', 'stock_update')
                    ->whereIn('user_id', $adminIds)
                    ->where('data_id', $catatan->id)
                    ->update([
                        'is_sent' => true,
                        'sent_at' => now(),
                    ]);
            }

        } catch (\Exception $e) {
            \Log::error('Failed to send FCM notification to admin: ' . $e->getMessage());
        }
    }
}
