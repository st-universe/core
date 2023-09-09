<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use Stu\Component\Map\EncodedMapInterface;
use Stu\Lib\Map\VisualPanel\VisualNavPanelEntry;
use Stu\Lib\Map\VisualPanel\VisualPanelEntryData;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

/**
 * Creates ship and ui related items
 */
final class ShipUiFactory implements ShipUiFactoryInterface
{
    private UserMapRepositoryInterface $userMapRepository;

    private ShipRepositoryInterface $shipRepository;

    private EncodedMapInterface $encodedMap;

    public function __construct(
        UserMapRepositoryInterface $userMapRepository,
        ShipRepositoryInterface $shipRepository,
        EncodedMapInterface $encodedMap
    ) {
        $this->userMapRepository = $userMapRepository;
        $this->shipRepository = $shipRepository;
        $this->encodedMap = $encodedMap;
    }

    public function createVisualNavPanel(
        ShipInterface $currentShip,
        UserInterface $user,
        LoggerUtilInterface $loggerUtil,
        bool $isTachyonSystemActive,
        bool $tachyonFresh
    ): VisualNavPanel {
        return new VisualNavPanel(
            $this,
            $this->userMapRepository,
            $this->shipRepository,
            $currentShip,
            $user,
            $loggerUtil,
            $isTachyonSystemActive,
            $tachyonFresh
        );
    }

    public function createVisualNavPanelEntry(
        VisualPanelEntryData $data,
        ?LayerInterface $layer,
        ShipInterface $currentShip,
        bool $isTachyonSystemActive = false,
        bool $tachyonFresh = false
    ): VisualNavPanelEntry {
        return new VisualNavPanelEntry(
            $data,
            $layer,
            $this->encodedMap,
            $currentShip,
            $isTachyonSystemActive,
            $tachyonFresh
        );
    }
}
