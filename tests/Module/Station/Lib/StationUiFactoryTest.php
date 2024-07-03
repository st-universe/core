<?php

declare(strict_types=1);

namespace Stu\Module\Station\Lib;

use Override;
use Mockery\MockInterface;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Orm\Entity\DockingPrivilegeInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class StationUiFactoryTest extends StuTestCase
{
    /** @var MockInterface&UserRepositoryInterface */
    private MockInterface $userRepository;

    /** @var MockInterface&AllianceRepositoryInterface */
    private MockInterface $allianceRepository;

    /** @var MockInterface&FactionRepositoryInterface */
    private MockInterface $factionRepository;

    /** @var MockInterface&ShipRepositoryInterface */
    private MockInterface $shipRepository;

    /** @var MockInterface&PanelLayerCreationInterface */
    private MockInterface $panelLayerCreation;

    private StationUiFactory $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->allianceRepository = $this->mock(AllianceRepositoryInterface::class);
        $this->factionRepository = $this->mock(FactionRepositoryInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->panelLayerCreation = $this->mock(PanelLayerCreationInterface::class);

        $this->subject = new StationUiFactory(
            $this->userRepository,
            $this->allianceRepository,
            $this->factionRepository,
            $this->shipRepository,
            $this->panelLayerCreation
        );
    }

    public function testCreateDockingPrivilegeItemReturnsInstance(): void
    {
        static::assertInstanceOf(
            DockingPrivilegeItem::class,
            $this->subject->createDockingPrivilegeItem(
                $this->mock(DockingPrivilegeInterface::class)
            )
        );
    }
}
