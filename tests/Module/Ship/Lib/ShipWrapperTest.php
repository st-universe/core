<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use JsonMapper\JsonMapperFactory;
use JsonMapper\JsonMapperInterface;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\StuTestCase;

class ShipWrapperTest extends StuTestCase
{
    private ShipInterface $ship;

    private ShipSystemManagerInterface $shipSystemManager;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private CancelRepairInterface $cancelRepair;

    private GameControllerInterface $game;

    private JsonMapperInterface $jsonMapper;

    private ShipWrapper $shipWrapper;

    private ShipSystemInterface $shipSystem;

    public function setUp(): void
    {
        //injected
        $this->ship = $this->mock(ShipInterface::class);
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->shipSystemRepository = $this->mock(ShipSystemRepositoryInterface::class);
        $this->colonyLibFactory = $this->mock(ColonyLibFactoryInterface::class);
        $this->cancelRepair = $this->mock(CancelRepairInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->jsonMapper = (new JsonMapperFactory())->bestFit();;

        $this->shipSystem = $this->mock(ShipSystemInterface::class);

        $this->shipWrapper = new ShipWrapper(
            $this->ship,
            $this->shipSystemManager,
            $this->shipRepository,
            $this->shipSystemRepository,
            $this->colonyLibFactory,
            $this->cancelRepair,
            $this->game,
            $this->jsonMapper
        );
    }

    public function testGetEpsShipSystem(): void
    {
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
            ->andReturn('{
                "battery": 1,
                "maxBattery": 55,
                "batteryCooldown": 42,
                "reloadBattery": true }
            ');

        $eps = $this->shipWrapper->getEpsShipSystem();

        $this->assertEquals(1, $eps->getBattery());
        $this->assertEquals(55, $eps->getMaxBattery());
        $this->assertEquals(42, $eps->getBatteryCooldown());
        $this->assertEquals(true, $eps->reloadBattery());
    }
}
