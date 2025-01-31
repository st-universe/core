<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Ui;

use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * Creates ship and ui related items
 */
interface ShipUiFactoryInterface
{
    public function createVisualNavPanel(
        SpacecraftInterface $currentSpacecraft,
        UserInterface $user,
        LoggerUtilInterface $loggerUtil,
        bool $tachyonFresh
    ): VisualNavPanel;
}
