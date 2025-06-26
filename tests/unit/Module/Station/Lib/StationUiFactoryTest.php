<?php

declare(strict_types=1);

namespace Stu\Module\Station\Lib;

use Mockery\MockInterface;
use Override;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Orm\Entity\DockingPrivilege;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class StationUiFactoryTest extends StuTestCase
{
    private MockInterface&UserRepositoryInterface $userRepository;

    private MockInterface&AllianceRepositoryInterface $allianceRepository;

    private MockInterface&FactionRepositoryInterface $factionRepository;

    private MockInterface&ShipRepositoryInterface $shipRepository;

    private MockInterface&PanelLayerCreationInterface $panelLayerCreation;

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
                $this->mock(DockingPrivilege::class)
            )
        );
    }
}
