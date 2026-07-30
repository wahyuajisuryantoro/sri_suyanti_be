<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pakan extends Model
{
    use HasFactory;
    
    protected $table = 'pakan';
    
    protected $fillable = [
        'jenis_pakan',
        'nama_pakan',
        'stok',
        'satuan',
        'deskripsi_pakan',
    ];
    
    protected $casts = [
        'stok' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function jadwalPakan()
    {
        return $this->hasMany(JadwalPakan::class);
    }

}
