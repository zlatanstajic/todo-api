<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\Timeline\TimelineDeleteFailedException;
use App\Exceptions\Timeline\TimelineNotFoundException;
use App\Http\Resources\TimelineResource;
use App\Services\TimelineService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Timeline Controller
 */
class TimelineController extends Controller
{
    public function __construct(public readonly TimelineService $timelineService)
    {
        //
    }

    /**
     * Get all timelines for a locale.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            return $this->successResponse(
                TimelineResource::collection(
                    $this->timelineService->getAllTimelines($this->locale($request))
                )
            );
        } catch (Exception $e) {
            return $this->exceptionResponse($e);
        }
    }

    /**
     * Get a specific timeline by slug.
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        try {
            $timeline = $this->timelineService->getTimelineBySlug($this->locale($request), $slug);

            throw_unless($timeline, TimelineNotFoundException::class);

            return $this->successResponse(new TimelineResource($timeline));
        } catch (Exception $e) {
            return $this->exceptionResponse($e);
        }
    }

    /**
     * Create a new timeline.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'locale' => ['required', 'string', 'in:en,sr'],
                'slug' => ['required', 'string', 'max:255'],
                'title' => ['required', 'string', 'max:255'],
                'tldr' => ['nullable', 'string'],
                'part_one' => ['required', 'array'],
                'part_one.*' => ['string'],
                'part_two' => ['required', 'array'],
                'part_two.*' => ['string'],
                'translation_key' => ['sometimes', 'string', 'max:255'],
            ]);

            $data['translation_key'] ??= $data['slug'];

            return $this->successResponse(
                data: new TimelineResource($this->timelineService->createTimeline($data)),
                code: Response::HTTP_CREATED
            );
        } catch (Exception $e) {
            return $this->exceptionResponse($e);
        }
    }

    /**
     * Update an existing timeline.
     */
    public function update(Request $request, string $slug): JsonResponse
    {
        try {
            $data = $request->validate([
                'title' => ['sometimes', 'required', 'string', 'max:255'],
                'tldr' => ['nullable', 'string'],
                'part_one' => ['sometimes', 'required', 'array'],
                'part_one.*' => ['string'],
                'part_two' => ['sometimes', 'required', 'array'],
                'part_two.*' => ['string'],
                'translation_key' => ['sometimes', 'required', 'string', 'max:255'],
            ]);

            return $this->successResponse(
                new TimelineResource(
                    $this->timelineService->updateTimeline($this->locale($request), $slug, $data)
                )
            );
        } catch (Exception $e) {
            return $this->exceptionResponse($e);
        }
    }

    /**
     * Delete a timeline.
     */
    public function destroy(Request $request, string $slug): JsonResponse
    {
        try {
            throw_unless(
                $this->timelineService->deleteTimeline($this->locale($request), $slug),
                TimelineDeleteFailedException::class
            );

            return $this->successResponse();
        } catch (Exception $e) {
            return $this->exceptionResponse($e);
        }
    }
}
