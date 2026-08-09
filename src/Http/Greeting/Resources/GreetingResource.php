<?php

declare(strict_types=1);

namespace VendorName\Skeleton\Http\Greeting\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $resource
 */
class GreetingResource extends JsonResource
{
    /**
     * @return array<string, string>
     */
    #[\Override]
    public function toArray($request): array
    {
        return [
            'message' => $this->resource,
        ];
    }
}
