<?php

namespace Stu\Component\Spacecraft\System\Control;

use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface AlertStateManagerInterface
{
    public function setAlertState(
        SpacecraftWrapperInterface|int $shipId,
        SpacecraftAlertStateEnum $alertState
    ): void;

    public function setAlertStateFleet(
        int $shipId,
        SpacecraftAlertStateEnum $alertState
    ): void;
}
