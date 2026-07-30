<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Obat extends Model
{
    use HasFactory;
    
    protected $table = 'obat';
    
    protected $fillable = [
        'jenis_obat',
        'nama_obat',
        'stok',
        'satuan',
        'deskripsi_obat',
    ];
    
    protected $casts = [
        'stok' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function jadwalObat()
    {
        return $this->hasMany(JadwalObat::class);
    }

}
