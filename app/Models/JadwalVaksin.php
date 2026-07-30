<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JadwalVaksin extends Model
{
    use HasFactory;
    
    protected $table = 'jadwal_vaksin';
    
    protected $fillable = [
        'jadwal_id',
        'vaksin_id',
        'jumlah_vaksin',
        'waktu',
    ];
    
    protected $casts = [
        'jumlah_vaksin' => 'integer',
        'waktu' => 'datetime:H:i:s',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function vaksin()
    {
        return $this->belongsTo(Vaksin::class);
    }

}
