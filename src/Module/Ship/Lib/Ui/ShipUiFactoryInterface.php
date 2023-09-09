<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use Stu\Lib\Map\VisualPanel\VisualNavPanelEntry;
use Stu\Lib\Map\VisualPanel\VisualPanelEntryData;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * Creates ship and ui related items
 */
interface ShipUiFactoryInterface
{
    public function createVisualNavPanel(
        ShipInterface $currentShip,
        UserInterface $user,
        LoggerUtilInterface $loggerUtil,
        bool $isTachyonSystemActive,
        bool $tachyonFresh
    ): VisualNavPanel;

    public function createVisualNavPanelEntry(
        VisualPanelEntryData $data,
        ?LayerInterface $layer,
        ShipInterface $currentShip,
        bool $isTachyonSystemActive = false,
        bool $tachyonFresh = false
    ): VisualNavPanelEntry;
}
