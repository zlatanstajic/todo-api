<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Timeline;
use App\Repositories\TimelineRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * TimelineService handles business logic related to timelines.
 */
class TimelineService
{
    public function __construct(public readonly TimelineRepository $timelineRepository)
    {
        //
    }

    /**
     * Get all timelines, optionally filtered by locale.
     *
     * @return Collection
     */
    public function getAllTimelines(?string $locale = null)
    {
        return $this->timelineRepository->getAll($locale);
    }

    /**
     * Get a timeline by locale and slug.
     */
    public function getTimelineBySlug(string $locale, string $slug): ?Timeline
    {
        return $this->timelineRepository->findBySlug($locale, $slug);
    }

    /**
     * Create a new timeline.
     */
    public function createTimeline(array $data): Timeline
    {
        return $this->timelineRepository->create($data);
    }

    /**
     * Update a timeline.
     */
    public function updateTimeline(string $locale, string $slug, array $data): Timeline
    {
        return $this->timelineRepository->update($locale, $slug, $data);
    }

    /**
     * Delete a timeline.
     */
    public function deleteTimeline(string $locale, string $slug): bool
    {
        return $this->timelineRepository->delete($locale, $slug);
    }
}
