<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Timeline;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Timeline Resource
 */
class TimelineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'type' => Timeline::class,
            'id' => $this->resource->id,
            'attributes' => [
                'locale' => $this->resource->locale,
                'slug' => $this->resource->slug,
                'title' => $this->resource->title,
                'tldr' => $this->resource->tldr,
                'part_one' => $this->resource->part_one,
                'part_two' => $this->resource->part_two,
                'figures' => $this->resource->figures->map(fn ($figure): array => [
                    'name' => $figure->name,
                    'slug' => $figure->slug,
                    'description' => $figure->description,
                ])->values()->all(),
                'translation_slug' => $this->resource->translation()?->slug,
                'created_at' => $this->resource->created_at?->toDateTimeString(),
                'updated_at' => $this->resource->updated_at?->toDateTimeString(),
            ],
        ];
    }
}
