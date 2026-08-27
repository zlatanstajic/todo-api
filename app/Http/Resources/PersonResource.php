<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Person Resource
 */
class PersonResource extends JsonResource
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
            'type' => 'person',
            'id' => $this->resource['slug'],
            'attributes' => [
                'slug' => $this->resource['slug'],
                'locale' => $this->resource['locale'],
                'name' => $this->resource['name'],
                'timelines' => $this->resource['timelines'],
            ],
        ];
    }
}
