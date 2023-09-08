<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\UserInterface;

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
        StarSystemInterface $systemForSensorScan = null
    ): VisualNavPanel;

    public function createVisualNavPanelEntry(
        VisualNavPanelEntryData $data = null,
        LayerInterface $layer = null,
        bool $isTachyonSystemActive = false,
        bool $tachyonFresh = false,
        ShipInterface $ship = null,
        StarSystemInterface $system = null
    ): VisualNavPanelEntry;

    public function createVisualNavPanelRow(): VisualNavPanelRow;
}
