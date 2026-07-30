<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;
    protected $table = 'notification';
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type',
        'data_id',
        'is_read',
        'is_sent',
        'sent_at',
    ];
    protected $casts = [
        'is_read' => 'boolean',
        'is_sent' => 'boolean',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
     public function user()
    {
        return $this->belongsTo(User::class);
    }

}
