<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Ui;

use Override;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

/**
 * Creates ship and ui related items
 */
final class ShipUiFactory implements ShipUiFactoryInterface
{
    public function __construct(
        private UserMapRepositoryInterface $userMapRepository,
        private PanelLayerCreationInterface $panelLayerCreation
    ) {}

    #[Override]
    public function createVisualNavPanel(
        SpacecraftWrapperInterface $wrapper,
        UserInterface $user,
        LoggerUtilInterface $loggerUtil,
        bool $tachyonFresh
    ): VisualNavPanel {
        return new VisualNavPanel(
            $this->panelLayerCreation,
            $this->userMapRepository,
            $wrapper,
            $user,
            $loggerUtil,
            $tachyonFresh
        );
    }
}
