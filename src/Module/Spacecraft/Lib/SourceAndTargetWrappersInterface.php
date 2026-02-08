<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

/**
 * @template T of SpacecraftWrapperInterface
 */
interface SourceAndTargetWrappersInterface
{
    /** @return T */
    public function getSource(): SpacecraftWrapperInterface;

    public function getTarget(): ?SpacecraftWrapperInterface;

    public function setTarget(?SpacecraftWrapperInterface $wrapper): void;
}
