<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Provider;

use Doctrine\Common\Collections\Collection;
use Mockery\MockInterface;
use Override;
use Stu\Component\Colony\ColonyPopulationCalculatorInterface;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Ship\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class ManagerProviderColonyTest extends StuTestCase
{
    /** @var MockInterface&ColonyInterface */
    private MockInterface $colony;

    /** @var MockInterface&CrewCreatorInterface */
    private MockInterface $crewCreator;

    /** @var MockInterface&ColonyLibFactoryInterface */
    private MockInterface $colonyLibFactory;

    /** @var MockInterface&ColonyStorageManagerInterface */
    private MockInterface $colonyStorageManager;

    /** @var MockInterface&TroopTransferUtilityInterface */
    private MockInterface $troopTransferUtility;

    private ManagerProviderInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->colony = $this->mock(ColonyInterface::class);
        $this->crewCreator = $this->mock(CrewCreatorInterface::class);
        $this->colonyLibFactory = $this->mock(ColonyLibFactoryInterface::class);
        $this->colonyStorageManager = $this->mock(ColonyStorageManagerInterface::class);
        $this->troopTransferUtility = $this->mock(TroopTransferUtilityInterface::class);

        $this->subject = new ManagerProviderColony(
            $this->colony,
            $this->crewCreator,
            $this->colonyLibFactory,
            $this->colonyStorageManager,
            $this->troopTransferUtility
        );
    }

    public function testGetUser(): void
    {
        $user = $this->mock(UserInterface::class);

        $this->colony->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->assertSame($user, $this->subject->getUser());
    }

    public function testGetEps(): void
    {
        $this->colony->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->assertEquals(42, $this->subject->getEps());
    }

    public function testLowerEps(): void
    {
        $this->colony->shouldReceive('lowerEps')
            ->with(5)
            ->once();

        $this->assertEquals($this->subject, $this->subject->lowerEps(5));
    }

    public function testGetName(): void
    {
        $this->colony->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('foo');

        $this->assertEquals('Kolonie foo', $this->subject->getName());
    }

    public function testGetSectorString(): void
    {
        $this->colony->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('foo');

        $this->assertEquals('foo', $this->subject->getSectorString());
    }

    public function testGetFreeCrewAmount(): void
    {
        $this->colony->shouldReceive('getCrewAssignmentAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(123);

        $this->assertEquals(123, $this->subject->getFreeCrewAmount());
    }

    public function testCreateShipCrew(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $this->crewCreator->shouldReceive('createShipCrew')
            ->with($ship, $this->colony, 42)
            ->once()
            ->andReturn(123);

        $this->subject->addShipCrew($ship, 42);
    }

    public function testGetFreeCrewStorage(): void
    {
        $populationCalculator = $this->mock(ColonyPopulationCalculatorInterface::class);

        $this->colonyLibFactory->shouldReceive('createColonyPopulationCalculator')
            ->with($this->colony)
            ->once()
            ->andReturn($populationCalculator);

        $populationCalculator->shouldReceive('getFreeAssignmentCount')
            ->withNoArgs()
            ->once()
            ->andReturn(4);

        $this->assertEquals(4, $this->subject->getFreeCrewStorage());
    }

    public function testAddCrewAssignments(): void
    {
        $crewAssignment = $this->mock(ShipCrewInterface::class);
        $crewAssignments = [$crewAssignment];

        $this->troopTransferUtility->shouldReceive('assignCrew')
            ->with($crewAssignment, $this->colony)
            ->once();

        $this->subject->addCrewAssignments($crewAssignments);
    }

    public function testGetStorage(): void
    {
        $storage = $this->mock(Collection::class);

        $this->colony->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);

        $this->assertSame($storage, $this->subject->getStorage());
    }

    public function testUpperStorage(): void
    {
        $commodity = $this->mock(CommodityInterface::class);

        $this->colonyStorageManager->shouldReceive('upperStorage')
            ->with($this->colony, $commodity, 5)
            ->once();

        $this->subject->upperStorage($commodity, 5);
    }

    public function testLowerStorage(): void
    {
        $commodity = $this->mock(CommodityInterface::class);

        $this->colonyStorageManager->shouldReceive('lowerStorage')
            ->with($this->colony, $commodity, 5)
            ->once();

        $this->subject->lowerStorage($commodity, 5);
    }
}
