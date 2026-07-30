<?php

namespace App\Models;

use App\Models\Obat;
use App\Models\User;
use App\Models\Pakan;
use App\Models\Vaksin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Catatan extends Model
{
   use HasFactory;

    protected $table = 'tbl_catatan';

    protected $fillable = [
        'user_id',
        'jenis_item',
        'item_id',
        'stok_sebelum',
        'stok_sesudah',
        'jumlah_perubahan',
        'jenis_perubahan',
        'catatan',
        'status',
        'tanggal_perubahan',
    ];

    protected $casts = [
        'stok_sebelum' => 'integer',
        'stok_sesudah' => 'integer',
        'jumlah_perubahan' => 'integer',
        'tanggal_perubahan' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pakan()
    {
        return $this->belongsTo(Pakan::class, 'item_id')->where('jenis_item', 'pakan');
    }

    public function obat()
    {
        return $this->belongsTo(Obat::class, 'item_id')->where('jenis_item', 'obat');
    }

    public function vaksin()
    {
        return $this->belongsTo(Vaksin::class, 'item_id')->where('jenis_item', 'vaksin');
    }

}
