<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penugasan extends Model
{
    use HasFactory;
    protected $table = 'penugasan';
     protected $fillable = [
        'judul',
        'deskripsi',
        'jenis_penugasan',
        'karyawan_id',
        'admin_id',
        'reference_id',
        'tanggal',
        'waktu',
        'status',
    ];
    public function karyawan()
    {
        return $this->belongsTo(User::class, 'karyawan_id');
    }
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'data_id')
            ->where('type', 'penugasan');
    }
}
