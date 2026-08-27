<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\Timeline\TimelineNotFoundException;
use App\Models\Timeline;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Timeline Repository
 */
class TimelineRepository
{
    /**
     * The model class name.
     */
    protected string $model = Timeline::class;

    /**
     * Get all timelines, optionally filtered by locale.
     */
    public function getAll(?string $locale = null): Collection
    {
        $query = $this->model::query()->with(['figures', 'translations']);

        if ($locale !== null) {
            $query->where('locale', $locale);
        }

        return $query->orderBy('slug')->get();
    }

    /**
     * Find a timeline by locale and slug.
     */
    public function findBySlug(string $locale, string $slug): ?Timeline
    {
        return $this->model::query()
            ->with(['figures', 'translations'])
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Create a new timeline.
     */
    public function create(array $data): Timeline
    {
        return $this->model::create($data);
    }

    /**
     * Update a timeline by locale and slug.
     *
     * @throws TimelineNotFoundException
     */
    public function update(string $locale, string $slug, array $data): Timeline
    {
        $timeline = $this->findBySlug($locale, $slug);

        throw_unless($timeline, TimelineNotFoundException::class);

        $timeline->update($data);

        return $timeline;
    }

    /**
     * Delete a timeline by locale and slug.
     */
    public function delete(string $locale, string $slug): bool
    {
        $timeline = $this->findBySlug($locale, $slug);

        if (! $timeline instanceof Timeline) {
            return false;
        }

        return (bool) $timeline->delete();
    }

    /**
     * Upsert a timeline keyed on (locale, slug) and replace its figures.
     *
     * @param  array<int, array{name: string, description: string}>  $figures
     */
    public function upsertWithFigures(array $attributes, array $figures): Timeline
    {
        return DB::transaction(function () use ($attributes, $figures): Timeline {
            $timeline = $this->model::query()->updateOrCreate(
                [
                    'locale' => $attributes['locale'],
                    'slug' => $attributes['slug'],
                ],
                $attributes
            );

            $timeline->figures()->delete();

            foreach ($figures as $figure) {
                $timeline->figures()->create([
                    'name' => $figure['name'],
                    'slug' => Str::slug($figure['name']),
                    'description' => $figure['description'],
                ]);
            }

            return $timeline;
        });
    }

    /**
     * Delete all timelines except the given slugs per locale.
     *
     * @param  array<string, array<int, string>>  $keptSlugsByLocale
     */
    public function pruneExcept(array $keptSlugsByLocale): int
    {
        $deleted = 0;

        foreach ($keptSlugsByLocale as $locale => $slugs) {
            $deleted += $this->model::query()
                ->where('locale', $locale)
                ->whereNotIn('slug', $slugs)
                ->delete();
        }

        return $deleted;
    }
}
