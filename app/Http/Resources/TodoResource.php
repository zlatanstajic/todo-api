<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Todo Resource
 */
class TodoResource extends JsonResource
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
            'type' => Todo::class,
            'id' => $this->resource->id,
            'attributes' => [
                'user_id' => $this->resource->user_id,
                'title' => $this->resource->title,
                'description' => $this->resource->description,
                'completed' => $this->resource->completed,
                'created_at' => $this->resource->created_at?->toDateTimeString(),
                'updated_at' => $this->resource->updated_at?->toDateTimeString(),
                'deleted_at' => $this->resource->deleted_at?->toDateTimeString(),
            ],
        ];
    }
}
