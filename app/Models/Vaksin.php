<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vaksin extends Model
{
     use HasFactory;
    
    protected $table = 'vaksin';
    
    protected $fillable = [
        'jenis_vaksin',
        'nama_vaksin',
        'stok',
        'satuan',
        'deskripsi_vaksin',
    ];
    
    protected $casts = [
        'stok' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function jadwalVaksin()
    {
        return $this->hasMany(JadwalVaksin::class);
    }
}
