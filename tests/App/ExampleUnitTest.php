<?php

declare(strict_types=1);

namespace VendorName\Skeleton\Tests\App;

use PHPUnit\Framework\TestCase;
use VendorName\Skeleton\Skeleton;

final class ExampleUnitTest extends TestCase
{
    public function test_it_can_be_instantiated_without_a_container(): void
    {
        $this->assertInstanceOf(Skeleton::class, new Skeleton);
    }
}
