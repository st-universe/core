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
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\StuTestCase;

class ShipWrapperFactoryTest extends StuTestCase
{
    private ShipSystemManagerInterface $shipSystemManager;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private CancelRepairInterface $cancelRepair;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private GameControllerInterface $game;

    private JsonMapperInterface $jsonMapper;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function setUp(): void
    {
        //injected
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->shipSystemRepository = $this->mock(ShipSystemRepositoryInterface::class);
        $this->colonyLibFactory = $this->mock(ColonyLibFactoryInterface::class);
        $this->cancelRepair = $this->mock(CancelRepairInterface::class);
        $this->torpedoTypeRepository = $this->mock(TorpedoTypeRepositoryInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->jsonMapper = (new JsonMapperFactory())->bestFit();

        $this->shipWrapperFactory = new ShipWrapperFactory(
            $this->shipSystemManager,
            $this->shipRepository,
            $this->shipSystemRepository,
            $this->colonyLibFactory,
            $this->cancelRepair,
            $this->torpedoTypeRepository,
            $this->game,
            $this->jsonMapper
        );
    }

    public function testWrapShips(): void
    {
        $shipA = $this->mock(ShipInterface::class);
        $shipB = $this->mock(ShipInterface::class);
        $shipArray = [12 => $shipA, 27 => $shipB];

        $result = $this->shipWrapperFactory->wrapShips($shipArray);

        $this->assertEquals(2, count($result));
        $this->assertEquals($shipA, $result[12]->get());
        $this->assertEquals($shipB, $result[27]->get());
    }

    public function testwrapShipsAsFleetIfSingleShipMode(): void
    {
        $shipA = $this->mock(ShipInterface::class);
        $shipB = $this->mock(ShipInterface::class);
        $shipArray = [12 => $shipA, 27 => $shipB];

        $fleetwrapper = $this->shipWrapperFactory->wrapShipsAsFleet($shipArray, true);

        $this->assertEquals(2, count($fleetwrapper->getShipWrappers()));
        $this->assertEquals($shipA, $fleetwrapper->getShipWrappers()[12]->get());
        $this->assertEquals($shipB, $fleetwrapper->getShipWrappers()[27]->get());
        $this->assertEquals('Einzelschiffe', $fleetwrapper->get()->getName());
        $this->assertEquals(PHP_INT_MAX, $fleetwrapper->get()->getSort());
    }

    public function testwrapShipsAsFleetIfNotSingleShipMode(): void
    {
        $shipA = $this->mock(ShipInterface::class);
        $shipB = $this->mock(ShipInterface::class);
        $user = $this->mock(UserInterface::class);
        $fleet = $this->mock(FleetInterface::class);

        $shipArray = [12 => $shipA, 27 => $shipB];

        $shipA->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $shipA->shouldReceive('getFleet')
            ->withNoArgs()
            ->twice()
            ->andReturn($fleet);
        $fleet->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('foo');
        $fleet->shouldReceive('getSort')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $fleet = $this->shipWrapperFactory->wrapShipsAsFleet($shipArray);

        $this->assertEquals(2, count($fleet->getShipWrappers()));
        $this->assertEquals($shipA, $fleet->getShipWrappers()[12]->get());
        $this->assertEquals($shipB, $fleet->getShipWrappers()[27]->get());
        $this->assertEquals('foo', $fleet->get()->getName());
        $this->assertEquals($user, $fleet->get()->getUser());
        $this->assertEquals(42, $fleet->get()->getSort());
    }
}
