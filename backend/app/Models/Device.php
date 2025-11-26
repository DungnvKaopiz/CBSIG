<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'device_uid',
        'name',
        'location',
        'status',
        'last_seen_at',
        'ip_address',
        'api_key',
        'firmware_version',
        'canvas_width',
        'canvas_height',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'integer',
        'canvas_width' => 'integer',
        'canvas_height' => 'integer',
        'last_seen_at' => 'datetime',
    ];
}
