<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Override;
class SourceAndTargetWrappers implements SourceAndTargetWrappersInterface
{
    private ?ShipWrapperInterface $target = null;

    public function __construct(private ShipWrapperInterface $source)
    {
    }

    #[Override]
    public function getSource(): ShipWrapperInterface
    {
        return $this->source;
    }

    #[Override]
    public function getTarget(): ?ShipWrapperInterface
    {
        return $this->target;
    }

    #[Override]
    public function setTarget(?ShipWrapperInterface $wrapper): void
    {
        $this->target = $wrapper;
    }
}
