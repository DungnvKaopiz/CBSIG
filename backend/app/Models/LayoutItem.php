<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LayoutItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'layout_id',
        'name',
        'content_id',
        'frame_metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'layout_id' => 'integer',
        'content_id' => 'integer',
        'frame_metadata' => 'array',
    ];

    /**
     * Get the layout that owns this layout item.
     */
    public function layout(): BelongsTo
    {
        return $this->belongsTo(Layout::class);
    }

    /**
     * Get the content associated with this layout item.
     * Note: Content model may not exist yet, uncomment when Content model is created
     */
    // public function content(): BelongsTo
    // {
    //     return $this->belongsTo(Content::class);
    // }
}

