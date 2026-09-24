<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Lib\Information\InformationInterface;

interface AccessCheckInterface
{
    public function checkUserAccess(
        ControllerInterface $controller,
        InformationInterface $info
    ): bool;

    public function isFeatureGranted(
        int $userId,
        AccessGrantedFeatureEnum $feature
    ): bool;
}
