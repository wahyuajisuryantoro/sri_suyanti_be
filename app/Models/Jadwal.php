<?php

namespace App\Models;

use App\Models\Periode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jadwal extends Model
{
   use HasFactory;
    
    protected $table = 'jadwal';
    
    protected $fillable = [
        'tgl_penjadwalan',
        'periode_id',
        'user_id',
        'status',
    ];
    
    protected $casts = [
        'tgl_penjadwalan' => 'date',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jadwalPakan()
    {
        return $this->hasMany(JadwalPakan::class);
    }

    public function jadwalObat()
    {
        return $this->hasMany(JadwalObat::class);
    }

    public function jadwalVaksin()
    {
        return $this->hasMany(JadwalVaksin::class);
    }

    // public function harian()
    // {
    //     return $this->hasMany(Harian::class);
    // }

}
