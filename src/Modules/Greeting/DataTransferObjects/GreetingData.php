<?php

declare(strict_types=1);

namespace VendorName\Skeleton\Modules\Greeting\DataTransferObjects;

use VendorName\Skeleton\Modules\Greeting\Enums\GreetingTone;

final readonly class GreetingData
{
    public function __construct(
        public string $name,
        public GreetingTone $tone = GreetingTone::Casual,
    ) {}
}
