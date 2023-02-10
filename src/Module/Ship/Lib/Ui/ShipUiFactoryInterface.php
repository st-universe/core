<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;


use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\UserInterface;
use VisualNavPanel;

/**
 * Creates ship and ui related items
 */
interface ShipUiFactoryInterface
{
    public function createVisualNavPanel(
        ShipInterface $ship,
        UserInterface $user,
        LoggerUtilInterface $loggerUtil,
        bool $isTachyonSystemActive,
        bool $tachyonFresh,
        StarSystemInterface $system = null
    ): VisualNavPanel;
}
