<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

interface SourceAndTargetWrappersInterface
{
    public function getSource(): ShipWrapperInterface;

    public function getTarget(): ?ShipWrapperInterface;

    public function setTarget(?ShipWrapperInterface $wrapper): void;
}
