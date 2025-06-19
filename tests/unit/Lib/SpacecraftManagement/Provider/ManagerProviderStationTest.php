<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Provider;

use Doctrine\Common\Collections\Collection;
use Mockery\MockInterface;
use Override;
use RuntimeException;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\CrewAssignmentInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class ManagerProviderStationTest extends StuTestCase
{
    private MockInterface&StationWrapperInterface $wrapper;
    private MockInterface&CrewCreatorInterface $crewCreator;
    private MockInterface&TroopTransferUtilityInterface $troopTransferUtility;
    private MockInterface&StorageManagerInterface $storageManager;

    private MockInterface&StationInterface $station;

    private ManagerProviderInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->wrapper = $this->mock(StationWrapperInterface::class);
        $this->crewCreator = $this->mock(CrewCreatorInterface::class);
        $this->troopTransferUtility = $this->mock(TroopTransferUtilityInterface::class);
        $this->storageManager = $this->mock(StorageManagerInterface::class);

        $this->station = $this->mock(StationInterface::class);

        $this->subject = new ManagerProviderStation(
            $this->wrapper,
            $this->crewCreator,
            $this->troopTransferUtility,
            $this->storageManager
        );
    }

    public function testGetUser(): void
    {
        $user = $this->mock(UserInterface::class);

        $this->wrapper->shouldReceive('get->getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->assertSame($user, $this->subject->getUser());
    }

    public function testGetEpsExpectZeroWhenNoEpsSystem(): void
    {
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->assertEquals(0, $this->subject->getEps());
    }

    public function testGetEpsExpectCorrectValueWhenEpsSystemExistent(): void
    {
        $eps = $this->mock(EpsSystemData::class);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($eps);
        $eps->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->assertEquals(42, $this->subject->getEps());
    }

    public function testLowerEpsExpectErrorWhenNoEpsInstalled(): void
    {
        static::expectExceptionMessage('can not lower eps without eps system');
        static::expectException(RuntimeException::class);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->lowerEps(5);
    }

    public function testLowerEpsExpectLoweringIfEpsInstalled(): void
    {
        $eps = $this->mock(EpsSystemData::class);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($eps);

        $eps->shouldReceive('lowerEps')
            ->with(5)
            ->once()
            ->andReturn($eps);
        $eps->shouldReceive('update')
            ->with()
            ->once();

        $this->assertSame($this->subject, $this->subject->lowerEps(5));
    }

    public function testGetName(): void
    {
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->station);

        $this->station->shouldReceive('getRump->getName')
            ->withNoArgs()
            ->once()
            ->andReturn('rumpname');
        $this->station->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('stationname');

        $this->assertEquals('rumpname stationname', $this->subject->getName());
    }

    public function testGetSectorString(): void
    {
        $this->wrapper->shouldReceive('get->getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('foo');

        $this->assertEquals('foo', $this->subject->getSectorString());
    }

    public function testGetFreeCrewAmount(): void
    {
        $this->wrapper->shouldReceive('get->getExcessCrewCount')
            ->withNoArgs()
            ->once()
            ->andReturn(123);

        $this->assertEquals(123, $this->subject->getFreeCrewAmount());
    }

    public function testCreateCrewAssignment(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $this->crewCreator->shouldReceive('createCrewAssignment')
            ->with($ship, $this->station, 42)
            ->once()
            ->andReturn(123);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->station);

        $this->subject->addCrewAssignment($ship, 42);
    }

    public function testGetFreeCrewStorage(): void
    {
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->station);

        $this->troopTransferUtility->shouldReceive('getFreeQuarters')
            ->with($this->station)
            ->once()
            ->andReturn(4);

        $this->assertEquals(4, $this->subject->GetFreeCrewStorage());
    }

    public function testAddCrewAssignments(): void
    {
        $crewAssignment = $this->mock(CrewAssignmentInterface::class);
        $crewAssignments = [$crewAssignment];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->station);

        $this->troopTransferUtility->shouldReceive('assignCrew')
            ->with($crewAssignment, $this->station)
            ->once();

        $this->subject->addCrewAssignments($crewAssignments);
    }

    public function testGetStorage(): void
    {
        $storage = $this->mock(Collection::class);

        $this->wrapper->shouldReceive('get->getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);

        $this->assertSame($storage, $this->subject->getStorage());
    }

    public function testUpperStorage(): void
    {
        $commodity = $this->mock(CommodityInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->station);

        $this->storageManager->shouldReceive('upperStorage')
            ->with($this->station, $commodity, 5)
            ->once();

        $this->subject->upperStorage($commodity, 5);
    }

    public function testLowerStorage(): void
    {
        $commodity = $this->mock(CommodityInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->station);

        $this->storageManager->shouldReceive('lowerStorage')
            ->with($this->station, $commodity, 5)
            ->once();

        $this->subject->lowerStorage($commodity, 5);
    }
}
