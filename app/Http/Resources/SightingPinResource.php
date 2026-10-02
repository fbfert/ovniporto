<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public API whitelist: exactly these seven fields leave the server. Anything
 * else on a report (description, member, consent, moderation) never does here.
 *
 * @property array{id: int, type: string, lat: float, lng: float, date: string, nickname: string, thumb: ?string} $resource
 */
class SightingPinResource extends JsonResource
{
    /** @return array{id: int, type: string, lat: float, lng: float, date: string, nickname: string, thumb: ?string} */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'type' => $this->resource['type'],
            'lat' => $this->resource['lat'],
            'lng' => $this->resource['lng'],
            'date' => $this->resource['date'],
            'nickname' => $this->resource['nickname'],
            'thumb' => $this->resource['thumb'],
        ];
    }
}
