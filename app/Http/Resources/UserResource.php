<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * User Resource
 */
class UserResource extends JsonResource
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
            'type' => User::class,
            'id' => $this->resource->id,
            'attributes' => [
                'name' => $this->resource->name,
                'email' => $this->resource->email,
                'created_at' => $this->resource->created_at?->toDateTimeString(),
                'updated_at' => $this->resource->updated_at?->toDateTimeString(),
            ],
        ];
    }
}
