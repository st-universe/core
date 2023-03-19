<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use Stu\Module\Logging\LoggerUtilInterface;
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

    /**
     * @param array{
     *     posx: int,
     *     posy: int,
     *     sysid: int,
     *     shipcount: int,
     *     cloakcount: int,
     *     allycolor: string,
     *     usercolor: string,
     *     factioncolor: string,
     *     type: int,
     *     d1c?: int,
     *     d2c?: int,
     *     d3c?: int,
     *     d4c?: int
     * } $entry
     */
    public function createVisualNavPanelEntry(
        array &$entry = [],
        bool $isTachyonSystemActive = false,
        bool $tachyonFresh = false,
        ShipInterface $ship = null,
        StarSystemInterface $system = null
    ): VisualNavPanelEntry;

    public function createVisualNavPanelRow(): VisualNavPanelRow;
}
