<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Ui;

use Mockery\MockInterface;
use Override;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class ShipUiFactoryTest extends StuTestCase
{
    private MockInterface&PanelLayerCreationInterface $panelLayerCreation;
    private MockInterface&PanelLayerConfiguration $panelLayerConfiguration;

    private ShipUiFactory $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->panelLayerCreation = $this->mock(PanelLayerCreationInterface::class);
        $this->panelLayerConfiguration = $this->mock(PanelLayerConfiguration::class);

        $this->subject = new ShipUiFactory(
            $this->panelLayerCreation,
            $this->panelLayerConfiguration
        );
    }

    public function testCreateVisualNavPanel(): void
    {
        static::assertInstanceOf(
            VisualNavPanel::class,
            $this->subject->createVisualNavPanel(
                $this->mock(SpacecraftWrapperInterface::class),
                $this->mock(User::class),
                $this->mock(LoggerUtilInterface::class),
                true,
            )
        );
    }
}
