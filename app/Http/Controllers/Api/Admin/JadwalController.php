<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Obat;
use App\Models\Pakan;
use App\Models\Jadwal;
use App\Models\Vaksin;
use App\Models\Periode;
use App\Models\JadwalObat;
use App\Models\UserDevice;
use App\Models\JadwalPakan;
use App\Models\JadwalVaksin;
use App\Models\Notification;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class JadwalController extends Controller
{
    protected $fcmService;

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Daftar semua jadwal (read-only) + periode & jumlah item pakan/obat/vaksin.
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $jadwal = Jadwal::with('periode')
                ->withCount(['jadwalPakan', 'jadwalObat', 'jadwalVaksin'])
                ->orderBy('tgl_penjadwalan', 'desc')
                ->get()
                ->map(function ($j) {
                    return [
                        'id' => $j->id,
                        'tgl_penjadwalan' => $j->tgl_penjadwalan,
                        'status' => $j->status,
                        'periode_id' => $j->periode_id,
                        'periode' => $j->periode ? [
                            'id' => $j->periode->id,
                            'tgl_mulai' => $j->periode->tgl_mulai,
                            'tgl_selesai' => $j->periode->tgl_selesai,
                            'total_ayam' => $j->periode->total_ayam,
                        ] : null,
                        'jumlah_pakan' => $j->jadwal_pakan_count,
                        'jumlah_obat' => $j->jadwal_obat_count,
                        'jumlah_vaksin' => $j->jadwal_vaksin_count,
                        'created_at' => $j->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Data jadwal berhasil diambil',
                'data' => $jadwal
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get jadwal data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeJadwal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'periode_id' => 'required|exists:periode,id',
            'tgl_penjadwalan' => 'required|date',
            'karyawan_ids' => 'required|array',
            'karyawan_ids.*' => 'exists:users,id',

            'jadwal_pakan' => 'nullable|array',
            'jadwal_pakan.*.pakan_id' => 'required_with:jadwal_pakan|exists:pakan,id',
            'jadwal_pakan.*.jumlah_pakan' => 'required_with:jadwal_pakan|integer|min:1',
            'jadwal_pakan.*.waktu' => 'required_with:jadwal_pakan',

            'jadwal_obat' => 'nullable|array',
            'jadwal_obat.*.obat_id' => 'required_with:jadwal_obat|exists:obat,id',
            'jadwal_obat.*.jumlah_obat' => 'required_with:jadwal_obat|integer|min:1',
            'jadwal_obat.*.waktu' => 'required_with:jadwal_obat',

            'jadwal_vaksin' => 'nullable|array',
            'jadwal_vaksin.*.vaksin_id' => 'required_with:jadwal_vaksin|exists:vaksin,id',
            'jadwal_vaksin.*.jumlah_vaksin' => 'required_with:jadwal_vaksin|integer|min:1',
            'jadwal_vaksin.*.waktu' => 'required_with:jadwal_vaksin',
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
            $existingJadwal = Jadwal::where('periode_id', $request->periode_id)
                ->where('tgl_penjadwalan', $request->tgl_penjadwalan)
                ->first();

            if ($existingJadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal untuk tanggal ini sudah ada'
                ], 422);
            }

            $jadwal = Jadwal::create([
                'tgl_penjadwalan' => $request->tgl_penjadwalan,
                'periode_id' => $request->periode_id,
                'user_id' => auth()->id(),
                'status' => 1,
            ]);

            $jadwalActivities = [];

            if ($request->has('jadwal_pakan') && !empty($request->jadwal_pakan)) {
                foreach ($request->jadwal_pakan as $pakanData) {
                    $jadwalPakan = JadwalPakan::create([
                        'jadwal_id' => $jadwal->id,
                        'pakan_id' => $pakanData['pakan_id'],
                        'jumlah_pakan' => $pakanData['jumlah_pakan'],
                        'waktu' => Carbon::parse($pakanData['waktu'])->format('H:i:s'),
                    ]);

                    $pakan = Pakan::find($pakanData['pakan_id']);
                    $jadwalActivities[] = [
                        'type' => 'pakan',
                        'name' => $pakan->nama_pakan,
                        'jumlah' => $pakanData['jumlah_pakan'],
                        'satuan' => $pakan->satuan,
                        'waktu' => $pakanData['waktu'],
                        'reference_id' => $jadwalPakan->id
                    ];
                }
            }
            if ($request->has('jadwal_obat') && !empty($request->jadwal_obat)) {
                foreach ($request->jadwal_obat as $obatData) {
                    $jadwalObat = JadwalObat::create([
                        'jadwal_id' => $jadwal->id,
                        'obat_id' => $obatData['obat_id'],
                        'jumlah_obat' => $obatData['jumlah_obat'],
                        'waktu' => Carbon::parse($obatData['waktu'])->format('H:i:s'),
                    ]);

                    $obat = Obat::find($obatData['obat_id']);
                    $jadwalActivities[] = [
                        'type' => 'obat',
                        'name' => $obat->nama_obat,
                        'jumlah' => $obatData['jumlah_obat'],
                        'satuan' => $obat->satuan,
                        'waktu' => $obatData['waktu'],
                        'reference_id' => $jadwalObat->id
                    ];
                }
            }

            if ($request->has('jadwal_vaksin') && !empty($request->jadwal_vaksin)) {
                foreach ($request->jadwal_vaksin as $vaksinData) {
                    $jadwalVaksin = JadwalVaksin::create([
                        'jadwal_id' => $jadwal->id,
                        'vaksin_id' => $vaksinData['vaksin_id'],
                        'jumlah_vaksin' => $vaksinData['jumlah_vaksin'],
                        'waktu' => Carbon::parse($vaksinData['waktu'])->format('H:i:s'),
                    ]);

                    $vaksin = Vaksin::find($vaksinData['vaksin_id']);
                    $jadwalActivities[] = [
                        'type' => 'vaksin',
                        'name' => $vaksin->nama_vaksin,
                        'jumlah' => $vaksinData['jumlah_vaksin'],
                        'satuan' => $vaksin->satuan,
                        'waktu' => $vaksinData['waktu'],
                        'reference_id' => $jadwalVaksin->id
                    ];
                }
            }

            $this->sendJadwalNotifications($request->karyawan_ids, $jadwal, $jadwalActivities);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil dibuat dan notifikasi dikirim',
                'data' => [
                    'jadwal' => $jadwal,
                    'activities' => $jadwalActivities,
                    'assigned_karyawan' => count($request->karyawan_ids)
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat jadwal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function sendJadwalNotifications(array $karyawanIds, Jadwal $jadwal, array $activities)
    {
        try {
           
            $tanggalFormatted = Carbon::parse($jadwal->tgl_penjadwalan)->locale('id')->isoFormat('dddd, D MMMM YYYY');
            $activitiesByTime = collect($activities)->groupBy('waktu')->map(function ($timeActivities) {
                return $timeActivities->map(function ($activity) {
                    $typeLabels = [
                        'pakan' => 'Pakan',
                        'obat' => 'Obat',
                        'vaksin' => 'Vaksin'
                    ];

                    $typeLabel = $typeLabels[$activity['type']] ?? $activity['type'];
                    return "{$typeLabel}: {$activity['name']} {$activity['jumlah']} {$activity['satuan']}";
                })->join(', ');
            });

            $scheduleText = $activitiesByTime->map(function ($activitiesText, $time) {
                $timeFormatted = Carbon::parse($time)->format('H:i');
                return "• {$timeFormatted} - {$activitiesText}";
            })->join("\n");

            $notificationTitle = "Jadwal Pemeliharaan";
            $notificationBody = "Jadwal untuk {$tanggalFormatted}:\n\n{$scheduleText}";

            \Log::info('Notification content prepared', [
                'title' => $notificationTitle,
                'body' => $notificationBody
            ]);
            foreach ($karyawanIds as $karyawanId) {
                $notification = Notification::create([
                    'user_id' => $karyawanId,
                    'title' => $notificationTitle,
                    'body' => $notificationBody,
                    'type' => 'jadwal_pemeliharaan',
                    'data_id' => $jadwal->id,
                    'is_read' => false,
                    'is_sent' => false,
                ]);
            }
            $data = [
                'type' => 'jadwal_pemeliharaan',
                'jadwal_id' => (string) $jadwal->id,
                'tanggal' => $jadwal->tgl_penjadwalan,
                'total_activities' => count($activities),
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ];
            $deviceTokens = UserDevice::whereIn('user_id', $karyawanIds)
                ->where('is_active', true)
                ->pluck('device_token')
                ->toArray();

            if (!empty($deviceTokens)) {
               

                $fcmResult = $this->fcmService->sendMultiNotification($deviceTokens, $notificationTitle, $notificationBody, $data);

              
                Notification::where('type', 'jadwal_pemeliharaan')
                    ->whereIn('user_id', $karyawanIds)
                    ->where('data_id', $jadwal->id)
                    ->update([
                        'is_sent' => true,
                        'sent_at' => now(),
                    ]);
            } else {
                
            }

        } catch (\Exception $e) {
           
        }
    }

    public function getPeriodes()
    {
        try {
            $periodes = Periode::where('status', 1)
                ->orderBy('tgl_mulai', 'desc')
                ->get(['id', 'tgl_mulai', 'tgl_selesai', 'total_ayam']);

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
}
