<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JadwalPakan extends Model
{
    use HasFactory;
    
    protected $table = 'jadwal_pakan';
    
    protected $fillable = [
        'jadwal_id',
        'pakan_id',
        'jumlah_pakan',
        'waktu',
    ];
    
    protected $casts = [
        'jumlah_pakan' => 'integer',
        'waktu' => 'datetime:H:i:s',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function pakan()
    {
        return $this->belongsTo(Pakan::class);
    }
}
