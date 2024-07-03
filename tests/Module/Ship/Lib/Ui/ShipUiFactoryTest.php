<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use Override;
use Mockery\MockInterface;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;
use Stu\StuTestCase;

class ShipUiFactoryTest extends StuTestCase
{
    /** @var MockInterface&UserMapRepositoryInterface */
    private MockInterface $userMapRepository;

    /** @var MockInterface&PanelLayerCreationInterface */
    private MockInterface $panelLayerCreation;

    private ShipUiFactory $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->userMapRepository = $this->mock(UserMapRepositoryInterface::class);
        $this->panelLayerCreation = $this->mock(PanelLayerCreationInterface::class);

        $this->subject = new ShipUiFactory(
            $this->userMapRepository,
            $this->panelLayerCreation
        );
    }

    public function testCreateVisualNavPanel(): void
    {
        static::assertInstanceOf(
            VisualNavPanel::class,
            $this->subject->createVisualNavPanel(
                $this->mock(ShipInterface::class),
                $this->mock(UserInterface::class),
                $this->mock(LoggerUtilInterface::class),
                true,
            )
        );
    }
}
