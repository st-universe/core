<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use JsonMapper\JsonMapperFactory;
use JsonMapper\JsonMapperInterface;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\Data\HullSystemData;
use Stu\Component\Ship\System\Data\ShipSystemDataFactoryInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\StuTestCase;

class ShipWrapperTest extends StuTestCase
{
    private ShipInterface $ship;

    private ShipSystemManagerInterface $shipSystemManager;

    private ShipRepositoryInterface $shipRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private CancelRepairInterface $cancelRepair;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private GameControllerInterface $game;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private ShipSystemDataFactoryInterface $shipSystemDataFactory;

    private JsonMapperInterface $jsonMapper;

    private ShipWrapper $shipWrapper;

    private ShipSystemInterface $shipSystem;

    public function setUp(): void
    {
        //injected
        $this->ship = $this->mock(ShipInterface::class);
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->colonyLibFactory = $this->mock(ColonyLibFactoryInterface::class);
        $this->cancelRepair = $this->mock(CancelRepairInterface::class);
        $this->torpedoTypeRepository = $this->mock(TorpedoTypeRepositoryInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->jsonMapper = (new JsonMapperFactory())->bestFit();
        $this->shipWrapperFactory = $this->mock(ShipWrapperFactoryInterface::class);
        $this->shipSystemDataFactory = $this->mock(ShipSystemDataFactoryInterface::class);

        $this->shipSystem = $this->mock(ShipSystemInterface::class);

        $this->shipWrapper = new ShipWrapper(
            $this->ship,
            $this->shipSystemManager,
            $this->shipRepository,
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
}
