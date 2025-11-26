<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Layout extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'canvas_width',
        'canvas_height',
        'created_by_user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'canvas_width' => 'integer',
        'canvas_height' => 'integer',
        'created_by_user_id' => 'integer',
    ];

    /**
     * Get the user that created this layout.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get the layout items (frames) for this layout.
     */
    public function layoutItems(): HasMany
    {
        return $this->hasMany(LayoutItem::class);
    }
}

