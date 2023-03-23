<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

class SourceAndTargetWrappers implements SourceAndTargetWrappersInterface
{
    private ShipWrapperInterface $source;
    private ?ShipWrapperInterface $target = null;

    public function __construct(ShipWrapperInterface $source)
    {
        $this->source = $source;
    }

    public function getSource(): ShipWrapperInterface
    {
        return $this->source;
    }

    public function getTarget(): ?ShipWrapperInterface
    {
        return $this->target;
    }

    public function setTarget(?ShipWrapperInterface $wrapper): void
    {
        $this->target = $wrapper;
    }
}
