<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Format tanggal ke bahasa Indonesia
     * 
     * @param mixed $tanggal
     * @return string
     */
    public static function formatTanggalIndonesia($tanggal): string
    {
        if (!$tanggal) return '';
        
        try {
            $date = Carbon::parse($tanggal);
            
            $daysIndo = [
                1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 
                5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
            ];
            
            $monthsIndo = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            
            $dayName = $daysIndo[$date->dayOfWeek] ?? '';
            $monthName = $monthsIndo[$date->month] ?? '';
            
            return "{$dayName}, {$date->day} {$monthName} {$date->year}";
            
        } catch (\Exception $e) {
            return $tanggal;
        }
    }

    /**
     * Format waktu ke bahasa Indonesia
     * 
     * @param mixed $waktu
     * @return string
     */
    public static function formatWaktuIndonesia($waktu): string
    {
        if (!$waktu) return '';
        
        try {
            $time = Carbon::parse($waktu);
            return $time->format('H.i') . ' WIB';
            
        } catch (\Exception $e) {
            // Fallback jika format waktu hanya HH:mm
            if (preg_match('/^(\d{1,2}):(\d{2})/', $waktu, $matches)) {
                return sprintf('%02d.%02d WIB', (int)$matches[1], (int)$matches[2]);
            }
            return $waktu;
        }
    }

    /**
     * Format datetime lengkap ke Indonesia
     * 
     * @param mixed $datetime
     * @return string
     */
    public static function formatDateTimeIndonesia($datetime): string
    {
        if (!$datetime) return '';
        
        try {
            $date = Carbon::parse($datetime);
            
            $tanggal = self::formatTanggalIndonesia($date);
            $waktu = self::formatWaktuIndonesia($date);
            
            return "{$tanggal} pukul {$waktu}";
            
        } catch (\Exception $e) {
            return $datetime;
        }
    }

    /**
     * Format tanggal singkat (hanya hari, tanggal bulan)
     * 
     * @param mixed $tanggal
     * @return string
     */
    public static function formatTanggalSingkat($tanggal): string
    {
        if (!$tanggal) return '';
        
        try {
            $date = Carbon::parse($tanggal);
            
            $daysIndo = [
                1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 
                5 => 'Jum', 6 => 'Sab', 7 => 'Min'
            ];
            
            $monthsIndo = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Ags',
                9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
            ];
            
            $dayName = $daysIndo[$date->dayOfWeek] ?? '';
            $monthName = $monthsIndo[$date->month] ?? '';
            
            return "{$dayName}, {$date->day} {$monthName}";
            
        } catch (\Exception $e) {
            return $tanggal;
        }
    }
}