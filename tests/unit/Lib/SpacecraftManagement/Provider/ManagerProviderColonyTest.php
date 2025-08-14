<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Provider;

use Doctrine\Common\Collections\Collection;
use Mockery\MockInterface;
use Override;
use Stu\Component\Colony\ColonyPopulationCalculatorInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class ManagerProviderColonyTest extends StuTestCase
{
    private MockInterface&Colony $colony;

    private MockInterface&CrewCreatorInterface $crewCreator;

    private MockInterface&ColonyLibFactoryInterface $colonyLibFactory;

    private MockInterface&StorageManagerInterface $storageManager;

    private MockInterface&TroopTransferUtilityInterface $troopTransferUtility;

    private ManagerProviderInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->colony = $this->mock(Colony::class);
        $this->crewCreator = $this->mock(CrewCreatorInterface::class);
        $this->colonyLibFactory = $this->mock(ColonyLibFactoryInterface::class);
        $this->storageManager = $this->mock(StorageManagerInterface::class);
        $this->troopTransferUtility = $this->mock(TroopTransferUtilityInterface::class);

        $this->subject = new ManagerProviderColony(
            $this->colony,
            $this->crewCreator,
            $this->colonyLibFactory,
            $this->storageManager,
            $this->troopTransferUtility
        );
    }

    public function testGetUser(): void
    {
        $user = $this->mock(User::class);

        $this->colony->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->assertSame($user, $this->subject->getUser());
    }

    public function testGetEps(): void
    {
        $this->colony->shouldReceive('getChangeable->getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->assertEquals(42, $this->subject->getEps());
    }

    public function testLowerEps(): void
    {
        $this->colony->shouldReceive('getChangeable->lowerEps')
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

    public function testCreateCrewAssignments(): void
    {
        $ship = $this->mock(Ship::class);

        $this->crewCreator->shouldReceive('createCrewAssignments')
            ->with($ship, $this->colony, 42)
            ->once()
            ->andReturn(123);

        $this->subject->addCrewAssignment($ship, 42);
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
        $crewAssignment = $this->mock(CrewAssignment::class);
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
        $commodity = $this->mock(Commodity::class);

        $this->storageManager->shouldReceive('upperStorage')
            ->with($this->colony, $commodity, 5)
            ->once();

        $this->subject->upperStorage($commodity, 5);
    }

    public function testLowerStorage(): void
    {
        $commodity = $this->mock(Commodity::class);

        $this->storageManager->shouldReceive('lowerStorage')
            ->with($this->colony, $commodity, 5)
            ->once();

        $this->subject->lowerStorage($commodity, 5);
    }
}
