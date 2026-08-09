<?php

declare(strict_types=1);

namespace VendorName\Skeleton\Tests\Integration;

use VendorName\Skeleton\Skeleton;
use VendorName\Skeleton\Tests\TestCase;

final class ExampleIntegrationTest extends TestCase
{
    protected function defineRoutes($router): void
    {
        $router->get('/skeleton-integration-test', fn () => response()->json([
            'resolved' => $this->app->make(Skeleton::class)::class,
        ]));
    }

    public function test_it_resolves_the_package_through_a_full_http_request(): void
    {
        // Act

        $testResponse = $this->get('/skeleton-integration-test');

        // Assert

        $testResponse->assertOk();

        $testResponse->assertJson([
            'resolved' => Skeleton::class,
        ]);
    }
}
