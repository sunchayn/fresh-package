<?php

declare(strict_types=1);

namespace Template\Contracts;

use Laravel\Chisel\Chisel;
use Template\Metadata;

/**
 * A single, named, selectable unit of configuration, either a package feature or a repository setting.
 * The term covers both kinds alike.
 */
interface ChoiceContract
{
    public static function key(): string;

    public function label(): string;

    public function onSelect(Chisel $chisel, Metadata $metadata): void;

    public function onDecline(Chisel $chisel, Metadata $metadata): void;

    /**
     * @return list<string>
     */
    public function manualSteps(): array;

    /**
     * @return list<class-string<ChoiceContract>> Choices that must run before this one.
     */
    public function dependsOn(): array;
}
