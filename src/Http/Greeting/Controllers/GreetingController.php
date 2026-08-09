<?php

declare(strict_types=1);

namespace VendorName\Skeleton\Http\Greeting\Controllers;

use VendorName\Skeleton\Http\Greeting\Requests\GreetingRequest;
use VendorName\Skeleton\Http\Greeting\Resources\GreetingResource;
use VendorName\Skeleton\Modules\Greeting\Actions\BuildGreetingAction;
use VendorName\Skeleton\Modules\Greeting\DataTransferObjects\GreetingData;
use VendorName\Skeleton\Modules\Greeting\Enums\GreetingTone;

class GreetingController
{
    public function __invoke(GreetingRequest $request, BuildGreetingAction $buildGreetingAction): GreetingResource
    {
        $data = new GreetingData(
            name: $request->string('name')->toString(),
            tone: GreetingTone::from($request->string('tone', GreetingTone::Casual->value)->toString()),
        );

        $result = $buildGreetingAction->execute($data);

        return GreetingResource::make($result);
    }
}
