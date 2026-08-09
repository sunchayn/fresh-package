<?php

declare(strict_types=1);

namespace Template\Choices;

use Laravel\Chisel\Chisel;
use Template\Contracts\ChoiceContract;
use Template\Metadata;

abstract class AbstractChoice implements ChoiceContract
{
    abstract public static function key(): string;

    abstract public function label(): string;

    public function onSelect(Chisel $chisel, Metadata $metadata): void
    {
        //
    }

    public function onDecline(Chisel $chisel, Metadata $metadata): void
    {
        //
    }

    /**
     * @return list<string>
     */
    public function manualSteps(): array
    {
        return [];
    }

    /**
     * @return class-string<AbstractChoice>[]
     */
    public function dependsOn(): array
    {
        return [];
    }
}
