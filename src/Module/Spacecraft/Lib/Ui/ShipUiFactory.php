<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Ui;

use Override;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * Creates ship and ui related items
 */
final class ShipUiFactory implements ShipUiFactoryInterface
{
    public function __construct(
        private PanelLayerCreationInterface $panelLayerCreation,
        private PanelLayerConfiguration $panelLayerConfiguration
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
            $this->panelLayerConfiguration,
            $wrapper,
            $user,
            $loggerUtil,
            $tachyonFresh
        );
    }
}
