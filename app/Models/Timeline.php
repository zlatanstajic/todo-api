<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Timeline Model
 *
 * @property int $id
 * @property string $locale
 * @property string $slug
 * @property string $title
 * @property string|null $tldr
 * @property array $part_one
 * @property array $part_two
 * @property string $translation_key
 * @property-read Collection<int, Timeline> $translations
 */
class Timeline extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'locale',
        'slug',
        'title',
        'tldr',
        'part_one',
        'part_two',
        'translation_key',
    ];

    /**
     * Get the figures that belong to the timeline.
     */
    public function figures(): HasMany
    {
        return $this->hasMany(Figure::class);
    }

    /**
     * Get all timelines sharing this timeline's translation key
     * (including this one). Eager-loadable, unlike translation().
     */
    public function translations(): HasMany
    {
        return $this->hasMany(self::class, 'translation_key', 'translation_key');
    }

    /**
     * Get the other-locale timeline with the same translation key.
     */
    public function translation(): ?self
    {
        return $this->translations
            ->first(fn (Timeline $timeline): bool => $timeline->locale !== $this->locale);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'part_one' => 'array',
            'part_two' => 'array',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
