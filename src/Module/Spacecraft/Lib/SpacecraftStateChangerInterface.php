<?php

namespace Stu\Module\Spacecraft\Lib;

use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;

interface SpacecraftStateChangerInterface
{
    public function changeState(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftStateEnum $newState
    ): void;

    public function changeAlertState(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftAlertStateEnum $alertState
    ): ?string;
}
