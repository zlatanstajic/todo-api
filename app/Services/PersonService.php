<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Figure;
use App\Repositories\FigureRepository;
use Illuminate\Support\Collection as SupportCollection;

/**
 * PersonService groups figures into per-locale people.
 */
class PersonService
{
    public function __construct(public readonly FigureRepository $figureRepository)
    {
        //
    }

    /**
     * Get all people for the given locale.
     *
     * @return SupportCollection<int, array<string, mixed>>
     */
    public function getAllPeople(string $locale): SupportCollection
    {
        return $this->figureRepository->getAllByLocale($locale)
            ->toBase()
            ->groupBy('slug')
            ->map(fn (SupportCollection $figures, $slug): array => $this->toPerson($locale, (string) $slug, $figures))
            ->values();
    }

    /**
     * Get a person by locale and slug.
     */
    public function getPersonBySlug(string $locale, string $slug): ?array
    {
        $figures = $this->figureRepository->getBySlug($locale, $slug);

        if ($figures->isEmpty()) {
            return null;
        }

        return $this->toPerson($locale, $slug, $figures->toBase());
    }

    /**
     * Build a person array from the figures sharing a slug.
     *
     * @param  SupportCollection<int, Figure>  $figures
     * @return array<string, mixed>
     */
    private function toPerson(string $locale, string $slug, SupportCollection $figures): array
    {
        /** @var Figure $first */
        $first = $figures->firstOrFail();

        return [
            'slug' => $slug,
            'locale' => $locale,
            'name' => $first->name,
            'timelines' => $figures->map(fn (Figure $figure): array => [
                'slug' => $figure->timeline->slug,
                'title' => $figure->timeline->title,
                'description' => $figure->description,
            ])->values()->all(),
        ];
    }
}
