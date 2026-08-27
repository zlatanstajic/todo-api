<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Figure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Figure Repository
 */
class FigureRepository
{
    /**
     * The model class name.
     */
    protected string $model = Figure::class;

    /**
     * Get all figures whose timeline matches the given locale.
     *
     * @return Collection<int, Figure>
     */
    public function getAllByLocale(string $locale): Collection
    {
        return $this->model::query()
            ->whereHas('timeline', fn (Builder $query) => $query->where('locale', $locale))
            ->with('timeline')
            ->orderBy('slug')
            ->get();
    }

    /**
     * Get all figures with the given slug whose timeline matches the locale.
     *
     * @return Collection<int, Figure>
     */
    public function getBySlug(string $locale, string $slug): Collection
    {
        return $this->model::query()
            ->whereHas('timeline', fn (Builder $query) => $query->where('locale', $locale))
            ->with('timeline')
            ->where('slug', $slug)
            ->orderBy('id')
            ->get();
    }
}
