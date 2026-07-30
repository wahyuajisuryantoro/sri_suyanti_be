<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\HomeAdmin;
use App\Http\Controllers\Api\PenugasanController;
use App\Http\Controllers\Api\Admin\JadwalController;
use App\Http\Controllers\Api\Admin\ManajemenKaryawan;
use App\Http\Controllers\Api\Admin\PeriodeController;
use App\Http\Controllers\Api\Admin\MasterDataController;
use App\Http\Controllers\Api\Karyawan\HomeKaryawanController;
use App\Http\Controllers\Api\Karyawan\NotificationController;
use App\Http\Controllers\Api\Karyawan\MasterDataKaryawanController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/update-fcm-token', [AuthController::class, 'updateFcmToken']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ADMIN ROUTES
    Route::get('/admin/dashboard', [HomeAdmin::class, 'getDashboardAdmin']);
    Route::get('/penugasan/get-karyawan', [PenugasanController::class, 'getKaryawanForPenugasan']);
    Route::post('/penugasan/store', [PenugasanController::class, 'storePenugasan']);
    Route::post('/penugasan/{id}/selesai', [PenugasanController::class, 'selesaikanPenugasan']);

    Route::prefix('admin/karyawan')->group(function () {
        Route::get('/', [ManajemenKaryawan::class, 'index']);
        Route::post('/', [ManajemenKaryawan::class, 'store']);
        Route::get('/{id}', [ManajemenKaryawan::class, 'show']);
        Route::delete('/{id}', [ManajemenKaryawan::class, 'destroy']);
    });

    Route::prefix('admin/master-data')->group(function () {
        Route::get('/pakan', [MasterDataController::class, 'getPakan']);
        Route::get('/obat', [MasterDataController::class, 'getObat']);
        Route::get('/vaksin', [MasterDataController::class, 'getVaksin']);
        Route::post('/pakan', [MasterDataController::class, 'storePakan']);
        Route::post('/obat', [MasterDataController::class, 'storeObat']);
        Route::post('/vaksin', [MasterDataController::class, 'storeVaksin']);
        Route::post('/update-stok', [MasterDataController::class, 'updateStok']);
    });

    Route::prefix('admin/periode')->group(function () {
        Route::get('/', [PeriodeController::class, 'index']);
        Route::post('/', [PeriodeController::class, 'store']);
        Route::get('/{id}', [PeriodeController::class, 'show']);
        Route::put('/{id}', [PeriodeController::class, 'update']);
        Route::delete('/{id}', [PeriodeController::class, 'destroy']);
        Route::get('/active/list', [PeriodeController::class, 'getActivePeriodes']);
        Route::get('/current/running', [PeriodeController::class, 'getCurrentPeriode']);
    });

    Route::prefix('admin/jadwal')->group(function () {
        Route::get('/', [JadwalController::class, 'index']);
        Route::post('/', [JadwalController::class, 'storeJadwal']);
        Route::get('/periodes', [JadwalController::class, 'getPeriodes']);
    });


    // KARYAWAN ROUTES
    Route::prefix('home-karyawan')->group(function () {
        Route::get('/data-karyawan', [HomeKaryawanController::class, 'getDataKaryawan']);
        Route::get('/status-penugasan', [HomeKaryawanController::class, 'getStatusPenugasan']);
        Route::get('/tugas-hari-ini', [HomeKaryawanController::class, 'getTugasHariIni']);
        Route::get('/dashboard-summary', [HomeKaryawanController::class, 'getDashboardSummary']);
        Route::get('/recent-tasks', [HomeKaryawanController::class, 'getRecentTasks']);
    });
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'getAllNotifications']);
        Route::get('/{id}', [NotificationController::class, 'show'])->whereNumber('id');
        Route::put('/read-all', [NotificationController::class, 'readAllNotifications']);
        Route::put('/{id}/read', [NotificationController::class, 'readNotification']);
        Route::delete('/{id}', [NotificationController::class, 'deleteNotification']);
        Route::delete('/all', [NotificationController::class, 'deleteAllNotifications']);
    });

    Route::prefix('karyawan')->group(function () {
        Route::get('/pakan', [MasterDataKaryawanController::class, 'getPakan']);
        Route::get('/obat', [MasterDataKaryawanController::class, 'getObat']);
        Route::get('/vaksin', [MasterDataKaryawanController::class, 'getVaksin']);
        Route::get('/jadwal-hari-ini', [MasterDataKaryawanController::class, 'getJadwalHariIni']);
        Route::post('/pakan/update-stok', [MasterDataKaryawanController::class, 'updateStokPakan']);
        Route::post('/obat/update-stok', [MasterDataKaryawanController::class, 'updateStokObat']);
        Route::post('/vaksin/update-stok', [MasterDataKaryawanController::class, 'updateStokVaksin']);
        Route::post('/update-stok-from-jadwal', [MasterDataKaryawanController::class, 'updateStokFromJadwal']);
    });
});