<?php

declare(strict_types=1);

namespace VendorName\Skeleton\Tests\App;

use VendorName\Skeleton\Skeleton;
use VendorName\Skeleton\Tests\TestCase;

final class ExampleFunctionalTest extends TestCase
{
    public function test_it_resolves_the_singleton(): void
    {
        $this->assertInstanceOf(Skeleton::class, $this->app->make(Skeleton::class));
    }

    public function test_it_returns_the_same_instance_from_the_container(): void
    {
        $this->assertSame($this->app->make(Skeleton::class), $this->app->make(Skeleton::class));
    }

    /* @chisel-config */
    public function test_it_merges_the_package_config(): void
    {
        $this->assertSame('default', config('skeleton.placeholder'));
    }

    /* @end-chisel-config */

    /* @chisel-translations */
    public function test_it_loads_the_package_translations(): void
    {
        $this->assertSame('Skeleton placeholder translation.', trans('skeleton::messages.placeholder'));
    }

    /* @end-chisel-translations */

    /* @chisel-views */
    public function test_it_loads_the_package_views(): void
    {
        $this->assertTrue(view()->exists('skeleton::placeholder'));
    }

    /* @end-chisel-views */

    /* @chisel-commands */
    public function test_it_registers_the_artisan_command(): void
    {
        $this->artisan('skeleton:placeholder')
            ->expectsOutputToContain('Hey, World!')
            ->assertSuccessful();
    }

    /* @end-chisel-commands */
}
