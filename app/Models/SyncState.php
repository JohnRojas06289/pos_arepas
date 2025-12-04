<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SyncState extends Model
{
    use HasUuids;

    protected $fillable = ['table_name', 'last_sync_at'];

    protected $casts = [
        'last_sync_at' => 'datetime',
    ];
}
