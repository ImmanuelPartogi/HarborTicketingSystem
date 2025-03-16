<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'statusable_id',
        'statusable_type',
        'old_status',
        'new_status',
        'reason',
        'notes',
        'admin_id',
    ];

    public function statusable()
    {
        return $this->morphTo();
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
