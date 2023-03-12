<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use JsonMapper\JsonMapperFactory;
use JsonMapper\JsonMapperInterface;
use Mockery\MockInterface;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\Data\HullSystemData;
use Stu\Component\Ship\System\Data\ShipSystemDataFactoryInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonyShipRepairInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\StuTestCase;

class ShipWrapperTest extends StuTestCase
{
    /**
     * @var MockInterface|ShipInterface
     */
    private ShipInterface $ship;

    /**
     * @var MockInterface|ShipSystemManagerInterface
     */
    private ShipSystemManagerInterface $shipSystemManager;

    private ShipRepositoryInterface $shipRepository;

    /**
     * @var MockInterface|ColonyShipRepairRepositoryInterface
     */
    private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private CancelRepairInterface $cancelRepair;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private GameControllerInterface $game;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private ShipSystemDataFactoryInterface $shipSystemDataFactory;

    private JsonMapperInterface $jsonMapper;

    private MockInterface $colonyFunctionManager;

    private ShipWrapper $shipWrapper;

    private ShipSystemInterface $shipSystem;

    public function setUp(): void
    {
        //injected
        $this->ship = $this->mock(ShipInterface::class);
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->colonyShipRepairRepository = $this->mock(ColonyShipRepairRepositoryInterface::class);
        $this->colonyLibFactory = $this->mock(ColonyLibFactoryInterface::class);
        $this->cancelRepair = $this->mock(CancelRepairInterface::class);
        $this->torpedoTypeRepository = $this->mock(TorpedoTypeRepositoryInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->jsonMapper = (new JsonMapperFactory())->bestFit();
        $this->shipWrapperFactory = $this->mock(ShipWrapperFactoryInterface::class);
        $this->shipSystemDataFactory = $this->mock(ShipSystemDataFactoryInterface::class);
        $this->colonyFunctionManager = $this->mock(ColonyFunctionManagerInterface::class);
        $this->shipSystem = $this->mock(ShipSystemInterface::class);

        $this->shipWrapper = new ShipWrapper(
            $this->colonyFunctionManager,
            $this->ship,
            $this->shipSystemManager,
            $this->shipRepository,
            $this->colonyShipRepairRepository,
            $this->colonyLibFactory,
            $this->cancelRepair,
            $this->torpedoTypeRepository,
            $this->game,
            $this->jsonMapper,
            $this->shipWrapperFactory,
            $this->shipSystemDataFactory
        );
    }

    public function testgetHullSystemData(): void
    {
        $hullSystemData = new HullSystemData();

        $this->shipSystemDataFactory->shouldReceive('createSystemData')
            ->with(ShipSystemTypeEnum::SYSTEM_HULL, $this->shipWrapperFactory)
            ->once()
            ->andReturn($hullSystemData);

        $hull = $this->shipWrapper->getHullSystemData();

        $this->assertEquals($hullSystemData, $hull);
    }

    public function testgetEpsSystemDataReturnNullIfSystemNotFound(): void
    {
        $this->ship->shouldReceive('hasShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_EPS)
            ->once()
            ->andReturn(false);

        $eps = $this->shipWrapper->getEpsSystemData();

        $this->assertNull($eps);
    }

    public function testgetEpsSystemDataWithDataEmptyExpectDefaultValues(): void
    {
        $shipSystemRepo = $this->mock(ShipSystemRepositoryInterface::class);
        $epsSystemData = new EpsSystemData($shipSystemRepo);

        $this->ship->shouldReceive('hasShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_EPS)
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_EPS)
            ->once()
            ->andReturn($this->shipSystem);
        $this->shipSystem->shouldReceive('getData')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $this->shipSystemDataFactory->shouldReceive('createSystemData')
            ->with(ShipSystemTypeEnum::SYSTEM_EPS, $this->shipWrapperFactory)
            ->once()
            ->andReturn($epsSystemData);

        $eps = $this->shipWrapper->getEpsSystemData();

        $this->assertEquals(0, $eps->getEps());
        $this->assertEquals(0, $eps->getTheoreticalMaxEps());
        $this->assertEquals(0, $eps->getBattery());
        $this->assertEquals(0, $eps->getMaxBattery());
        $this->assertEquals(0, $eps->getBatteryCooldown());
        $this->assertEquals(false, $eps->reloadBattery());
    }

    public function testgetEpsSystemDataWithDataNotEmptyExpectCorrectValues(): void
    {
        $shipSystemRepo = $this->mock(ShipSystemRepositoryInterface::class);
        $epsSystemData = new EpsSystemData($shipSystemRepo);

        $this->ship->shouldReceive('hasShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_EPS)
            ->twice()
            ->andReturn(true);
        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_EPS)
            ->once()
            ->andReturn($this->shipSystem);
        $this->shipSystemDataFactory->shouldReceive('createSystemData')
            ->with(ShipSystemTypeEnum::SYSTEM_EPS, $this->shipWrapperFactory)
            ->once()
            ->andReturn($epsSystemData);
        $this->shipSystem->shouldReceive('getData')
            ->withNoArgs()
            ->once()
            ->andReturn('{
                "eps": 13,
                "maxEps": 27,
                "battery": 1,
                "maxBattery": 55,
                "batteryCooldown": 42,
                "reloadBattery": true }
            ');

        // call two times to check if cache works
        $eps = $this->shipWrapper->getEpsSystemData();
        $eps = $this->shipWrapper->getEpsSystemData();

        $this->assertEquals($epsSystemData, $eps);
        $this->assertEquals(13, $eps->getEps());
        $this->assertEquals(27, $eps->getTheoreticalMaxEps());
        $this->assertEquals(1, $eps->getBattery());
        $this->assertEquals(55, $eps->getMaxBattery());
        $this->assertEquals(42, $eps->getBatteryCooldown());
        $this->assertEquals(true, $eps->reloadBattery());
    }

    public function testGetRepairDurationWithIntactShipExpectZero(): void
    {
        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()->once()->andReturn(100);
        $this->ship->shouldReceive('getHull')
            ->withNoArgs()->once()->andReturn(100);
        $this->ship->shouldReceive('getRepairRate')
            ->withNoArgs()->once()->andReturn(10);
        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()->once()->andReturn(new ArrayCollection());

        $this->ship->shouldReceive('getId')
            ->withNoArgs()->once()->andReturn(42);

        $this->colonyShipRepairRepository->shouldReceive('getByShip')
            ->with(42)->once()->andReturn(null);

        $duration = $this->shipWrapper->getRepairDuration();

        $this->assertEquals(0, $duration);
    }

    public function testGetRepairDurationWithDamagedHull(): void
    {
        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()->once()->andReturn(100);
        $this->ship->shouldReceive('getHull')
            ->withNoArgs()->once()->andReturn(79);
        $this->ship->shouldReceive('getRepairRate')
            ->withNoArgs()->once()->andReturn(10);
        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()->once()->andReturn(new ArrayCollection());

        $this->ship->shouldReceive('getId')
            ->withNoArgs()->once()->andReturn(42);

        $this->colonyShipRepairRepository->shouldReceive('getByShip')
            ->with(42)->once()->andReturn(null);

        $duration = $this->shipWrapper->getRepairDuration();

        $this->assertEquals(3, $duration);
    }

    public function testGetRepairDurationWithDamagedSystems(): void
    {
        $damagedSystem1 = $this->mock(ShipSystemInterface::class);
        $damagedSystem1->shouldReceive('getStatus')
            ->withNoArgs()->andReturn(1);
        $damagedSystem1->shouldReceive('getSystemType')
            ->withNoArgs()->andReturn(1);
        $shipSystemType1 = $this->mock(ShipSystemTypeInterface::class);
        $this->shipSystemManager->shouldReceive('lookupSystem')
            ->with(1)->once()->andReturn($shipSystemType1);
        $shipSystemType1->shouldReceive('getPriority')
            ->withNoArgs()->once()->andReturn(1);

        $damagedSystem2 = $this->mock(ShipSystemInterface::class);
        $damagedSystem2->shouldReceive('getStatus')
            ->withNoArgs()->andReturn(2);
        $damagedSystem2->shouldReceive('getSystemType')
            ->withNoArgs()->andReturn(2);
        $shipSystemType2 = $this->mock(ShipSystemTypeInterface::class);
        $this->shipSystemManager->shouldReceive('lookupSystem')
            ->with(2)->once()->andReturn($shipSystemType2);
        $shipSystemType2->shouldReceive('getPriority')
            ->withNoArgs()->once()->andReturn(2);


        $systems = new ArrayCollection();
        $systems->add($damagedSystem1);
        $systems->add($damagedSystem2);

        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()->once()->andReturn(100);
        $this->ship->shouldReceive('getHull')
            ->withNoArgs()->once()->andReturn(100);
        $this->ship->shouldReceive('getRepairRate')
            ->withNoArgs()->once()->andReturn(10);
        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()->once()->andReturn($systems);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()->once()->andReturn(42);

        $this->colonyShipRepairRepository->shouldReceive('getByShip')
            ->with(42)->once()->andReturn(null);

        $duration = $this->shipWrapper->getRepairDuration();

        $this->assertEquals(1, $duration);
    }

    public function testGetRepairDurationWithDamagedSystemsAndInactiveRepairStation(): void
    {
        $systems = new ArrayCollection();

        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()->once()->andReturn(100);
        $this->ship->shouldReceive('getHull')
            ->withNoArgs()->once()->andReturn(60);
        $this->ship->shouldReceive('getRepairRate')
            ->withNoArgs()->once()->andReturn(10);
        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()->once()->andReturn($systems);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()->once()->andReturn(42);

        $colonyShipRepair = $this->mock(ColonyShipRepairInterface::class);
        $colony = $this->mock(ColonyInterface::class);

        $this->colonyShipRepairRepository->shouldReceive('getByShip')
            ->with(42)->once()->andReturn($colonyShipRepair);

        $colonyShipRepair->shouldReceive('getColony')
            ->withNoArgs()
            ->once()
            ->andReturn($colony);

        $this->colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with($colony, BuildingEnum::BUILDING_FUNCTION_REPAIR_SHIPYARD)
            ->once()
            ->andReturnFalse();

        $duration = $this->shipWrapper->getRepairDuration();

        $this->assertEquals(4, $duration);
    }

    public function testGetRepairDurationWithDamagedSystemsAndActiveRepairStation(): void
    {
        $damagedSystem1 = $this->mock(ShipSystemInterface::class);
        $damagedSystem1->shouldReceive('getStatus')
            ->withNoArgs()->andReturn(1);
        $damagedSystem1->shouldReceive('getSystemType')
            ->withNoArgs()->andReturn(1);
        $shipSystemType1 = $this->mock(ShipSystemTypeInterface::class);
        $this->shipSystemManager->shouldReceive('lookupSystem')
            ->with(1)->once()->andReturn($shipSystemType1);
        $shipSystemType1->shouldReceive('getPriority')
            ->withNoArgs()->once()->andReturn(1);

        $damagedSystem2 = $this->mock(ShipSystemInterface::class);
        $damagedSystem2->shouldReceive('getStatus')
            ->withNoArgs()->andReturn(2);
        $damagedSystem2->shouldReceive('getSystemType')
            ->withNoArgs()->andReturn(2);
        $shipSystemType2 = $this->mock(ShipSystemTypeInterface::class);
        $this->shipSystemManager->shouldReceive('lookupSystem')
            ->with(2)->once()->andReturn($shipSystemType2);
        $shipSystemType2->shouldReceive('getPriority')
            ->withNoArgs()->once()->andReturn(2);

        $damagedSystem3 = $this->mock(ShipSystemInterface::class);
        $damagedSystem3->shouldReceive('getStatus')
            ->withNoArgs()->andReturn(3);
        $damagedSystem3->shouldReceive('getSystemType')
            ->withNoArgs()->andReturn(3);
        $shipSystemType3 = $this->mock(ShipSystemTypeInterface::class);
        $this->shipSystemManager->shouldReceive('lookupSystem')
            ->with(3)->once()->andReturn($shipSystemType3);
        $shipSystemType3->shouldReceive('getPriority')
            ->withNoArgs()->once()->andReturn(3);

        $damagedSystem4 = $this->mock(ShipSystemInterface::class);
        $damagedSystem4->shouldReceive('getStatus')
            ->withNoArgs()->andReturn(4);
        $damagedSystem4->shouldReceive('getSystemType')
            ->withNoArgs()->andReturn(4);
        $shipSystemType4 = $this->mock(ShipSystemTypeInterface::class);
        $this->shipSystemManager->shouldReceive('lookupSystem')
            ->with(4)->once()->andReturn($shipSystemType4);
        $shipSystemType4->shouldReceive('getPriority')
            ->withNoArgs()->once()->andReturn(4);


        $systems = new ArrayCollection();
        $systems->add($damagedSystem1);
        $systems->add($damagedSystem2);
        $systems->add($damagedSystem3);
        $systems->add($damagedSystem4);

        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()->once()->andReturn(100);
        $this->ship->shouldReceive('getHull')
            ->withNoArgs()->once()->andReturn(80);
        $this->ship->shouldReceive('getRepairRate')
            ->withNoArgs()->once()->andReturn(10);
        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()->once()->andReturn($systems);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()->once()->andReturn(42);

        $colonyShipRepair = $this->mock(ColonyShipRepairInterface::class);
        $colony = $this->mock(ColonyInterface::class);

        $this->colonyShipRepairRepository->shouldReceive('getByShip')
            ->with(42)->once()->andReturn($colonyShipRepair);
        $colonyShipRepair->shouldReceive('getColony')
            ->withNoArgs()
            ->once()
            ->andReturn($colony);

        $this->colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with($colony, BuildingEnum::BUILDING_FUNCTION_REPAIR_SHIPYARD)
            ->once()
            ->andReturnTrue();

        $duration = $this->shipWrapper->getRepairDuration();

        $this->assertEquals(1, $duration);
    }

    public function testGetRepairDurationPreviewWithDamagedHullAndNotOverColony(): void
    {
        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()->once()->andReturn(100);
        $this->ship->shouldReceive('getHull')
            ->withNoArgs()->once()->andReturn(60);
        $this->ship->shouldReceive('getRepairRate')
            ->withNoArgs()->once()->andReturn(10);
        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()->once()->andReturn(new ArrayCollection());

        $this->ship->shouldReceive('isOverColony')
            ->withNoArgs()->once()->andReturn(null);

        $duration = $this->shipWrapper->getRepairDurationPreview();

        $this->assertEquals(4, $duration);
    }

    public function testGetRepairDurationPreviewWithDamagedHullAndOverColonyWithInactiveRepairStation(): void
    {
        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()->once()->andReturn(100);
        $this->ship->shouldReceive('getHull')
            ->withNoArgs()->once()->andReturn(60);
        $this->ship->shouldReceive('getRepairRate')
            ->withNoArgs()->once()->andReturn(10);
        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()->once()->andReturn(new ArrayCollection());

        $colony = $this->mock(ColonyInterface::class);

        $this->colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with($colony, BuildingEnum::BUILDING_FUNCTION_REPAIR_SHIPYARD)
            ->once()
            ->andReturnFalse();

        $this->ship->shouldReceive('isOverColony')
            ->withNoArgs()->once()->andReturn($colony);

        $duration = $this->shipWrapper->getRepairDurationPreview();

        $this->assertEquals(4, $duration);
    }

    public function testGetRepairDurationPreviewWithDamagedHullAndOverColonyWithActiveRepairStation(): void
    {
        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()->once()->andReturn(100);
        $this->ship->shouldReceive('getHull')
            ->withNoArgs()->once()->andReturn(50);
        $this->ship->shouldReceive('getRepairRate')
            ->withNoArgs()->once()->andReturn(10);
        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()->once()->andReturn(new ArrayCollection());

        $colony = $this->mock(ColonyInterface::class);

        $this->colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with($colony, BuildingEnum::BUILDING_FUNCTION_REPAIR_SHIPYARD)
            ->once()
            ->andReturnTrue();

        $this->ship->shouldReceive('isOverColony')
            ->withNoArgs()->once()->andReturn($colony);

        $duration = $this->shipWrapper->getRepairDurationPreview();

        $this->assertEquals(3, $duration);
    }
}
