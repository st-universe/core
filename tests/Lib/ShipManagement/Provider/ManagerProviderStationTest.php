<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mockery\MockInterface;
use RuntimeException;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class ManagerProviderStationTest extends StuTestCase
{
    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface&CrewCreatorInterface */
    private MockInterface $crewCreator;

    /** @var MockInterface&TroopTransferUtilityInterface */
    private MockInterface $troopTransferUtility;

    /** @var MockInterface&ShipStorageManagerInterface */
    private MockInterface $shipStorageManager;

    private ShipInterface $station;

    private ManagerProviderInterface $subject;

    protected function setUp(): void
    {
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->crewCreator = $this->mock(CrewCreatorInterface::class);
        $this->troopTransferUtility = $this->mock(TroopTransferUtilityInterface::class);
        $this->shipStorageManager = $this->mock(ShipStorageManagerInterface::class);

        $this->station = $this->mock(ShipInterface::class);

        $this->subject = new ManagerProviderStation(
            $this->wrapper,
            $this->crewCreator,
            $this->troopTransferUtility,
            $this->shipStorageManager
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

    public function testCreateShipCrew(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $this->crewCreator->shouldReceive('createShipCrew')
            ->with($ship, $this->station, 42)
            ->once()
            ->andReturn(123);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->station);

        $this->subject->addShipCrew($ship, 42);
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
        $crewAssignment = $this->mock(ShipCrewInterface::class);
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

        $this->shipStorageManager->shouldReceive('upperStorage')
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

        $this->shipStorageManager->shouldReceive('lowerStorage')
            ->with($this->station, $commodity, 5)
            ->once();

        $this->subject->lowerStorage($commodity, 5);
    }
}
