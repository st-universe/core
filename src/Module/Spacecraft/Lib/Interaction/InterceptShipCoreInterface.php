<?php

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface InterceptShipCoreInterface
{
    public function intercept(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftWrapperInterface $targetWrapper,
        InformationInterface $informations
    ): void;
}
