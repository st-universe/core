<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserLayerRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

/**
 * Creates ship and ui related items
 */
final class ShipUiFactory implements ShipUiFactoryInterface
{
    private UserLayerRepositoryInterface $userLayerRepository;

    private UserMapRepositoryInterface $userMapRepository;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        UserLayerRepositoryInterface $userLayerRepository,
        UserMapRepositoryInterface $userMapRepository,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->userLayerRepository = $userLayerRepository;
        $this->userMapRepository = $userMapRepository;
        $this->shipRepository = $shipRepository;
    }

    public function createVisualNavPanel(
        ShipInterface $ship,
        UserInterface $user,
        LoggerUtilInterface $loggerUtil,
        bool $isTachyonSystemActive,
        bool $tachyonFresh,
        StarSystemInterface $system = null
    ): VisualNavPanel {
        return new VisualNavPanel(
            $this,
            $this->userLayerRepository,
            $this->userMapRepository,
            $this->shipRepository,
            $ship,
            $user,
            $loggerUtil,
            $isTachyonSystemActive,
            $tachyonFresh,
            $system
        );
    }

    public function createVisualNavPanelEntry(
        array &$entry = [],
        bool $isTachyonSystemActive = false,
        bool $tachyonFresh = false,
        ShipInterface $ship = null,
        StarSystemInterface $system = null
    ): VisualNavPanelEntry {
        return new VisualNavPanelEntry(
            $entry,
            $isTachyonSystemActive,
            $tachyonFresh,
            $ship,
            $system
        );
    }

    public function createVisualNavPanelRow(): VisualNavPanelRow
    {
        return new VisualNavPanelRow();
    }
}
