<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Periode extends Model
{
   use HasFactory;
    
    protected $table = 'periode';
    
    protected $fillable = [
        'tgl_mulai',
        'tgl_selesai',
        'total_ayam',
        'status',
    ];
    
    protected $casts = [
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
        'total_ayam' => 'integer',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function jadwal()
    {
        return $this->hasMany(Jadwal::class);
    }
}
