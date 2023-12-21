<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

/**
 * Creates ship and ui related items
 */
final class ShipUiFactory implements ShipUiFactoryInterface
{
    private UserMapRepositoryInterface $userMapRepository;

    private PanelLayerCreationInterface $panelLayerCreation;

    public function __construct(
        UserMapRepositoryInterface $userMapRepository,
        PanelLayerCreationInterface $panelLayerCreation
    ) {
        $this->userMapRepository = $userMapRepository;
        $this->panelLayerCreation = $panelLayerCreation;
    }

    public function createVisualNavPanel(
        ShipInterface $currentShip,
        UserInterface $user,
        LoggerUtilInterface $loggerUtil,
        bool $tachyonFresh
    ): VisualNavPanel {
        return new VisualNavPanel(
            $this->panelLayerCreation,
            $this->userMapRepository,
            $currentShip,
            $user,
            $loggerUtil,
            $tachyonFresh
        );
    }
}
