<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Figure Model
 *
 * @property int $id
 * @property int $timeline_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property-read Timeline $timeline
 */
class Figure extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'timeline_id',
        'name',
        'slug',
        'description',
    ];

    /**
     * Get the timeline that the figure belongs to.
     */
    public function timeline(): BelongsTo
    {
        return $this->belongsTo(Timeline::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
