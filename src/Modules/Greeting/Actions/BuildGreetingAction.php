<?php

declare(strict_types=1);

namespace VendorName\Skeleton\Modules\Greeting\Actions;

use VendorName\Skeleton\Modules\Greeting\DataTransferObjects\GreetingData;
use VendorName\Skeleton\Modules\Greeting\Enums\GreetingTone;

final class BuildGreetingAction
{
    public function execute(GreetingData $data): string
    {
        $salutation = match ($data->tone) {
            GreetingTone::Formal => 'Good day',
            GreetingTone::Casual => 'Hey',
        };

        return sprintf('%s, %s!', $salutation, $data->name);
    }
}
