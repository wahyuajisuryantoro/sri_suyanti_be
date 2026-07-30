<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kontak',
        'alamat',
        'tgl_aktif',
        'gambar',
    ];

    protected function casts(): array
    {
        return [
            'tgl_aktif' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
