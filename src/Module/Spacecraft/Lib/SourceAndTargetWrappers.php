<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Override;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

/**
 * @template T of SpacecraftWrapperInterface
 * 
 * @implements SourceAndTargetWrappersInterface<T>
 */
class SourceAndTargetWrappers implements SourceAndTargetWrappersInterface
{
    private ?SpacecraftWrapperInterface $target = null;

    /**
     * @param T $source
     */
    public function __construct(private $source) {}

    #[Override]
    public function getSource(): SpacecraftWrapperInterface
    {
        return $this->source;
    }

    #[Override]
    public function getTarget(): ?SpacecraftWrapperInterface
    {
        return $this->target;
    }

    #[Override]
    public function setTarget(?SpacecraftWrapperInterface $wrapper): void
    {
        $this->target = $wrapper;
    }
}
