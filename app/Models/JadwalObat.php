<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JadwalObat extends Model
{
     use HasFactory;
    
    protected $table = 'jadwal_obat';
    
    protected $fillable = [
        'jadwal_id',
        'obat_id',
        'jumlah_obat',
        'waktu',
    ];
    
    protected $casts = [
        'jumlah_obat' => 'integer',
        'waktu' => 'datetime:H:i:s',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }
}
