<?php

declare(strict_types=1);

namespace Stu\Module\Control;

interface AccessCheckInterface
{
    public function checkUserAccess(
        ControllerInterface $controller,
        GameControllerInterface $game
    ): bool;

    public function isFeatureGranted(
        int $userId,
        AccessGrantedFeatureEnum $feature,
        GameControllerInterface $game
    ): bool;
}
