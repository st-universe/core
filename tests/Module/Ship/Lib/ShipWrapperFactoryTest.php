<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use JBBCode\Parser;
use Override;
use Stu\Component\Ship\Repair\RepairUtilInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\SystemDataDeserializerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Ui\StateIconAndTitle;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class ShipWrapperFactoryTest extends StuTestCase
{
    private ShipSystemManagerInterface $shipSystemManager;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private GameControllerInterface $game;

    private ShipStateChangerInterface $shipStateChanger;

    private RepairUtilInterface $repairUtil;

    private UserRepositoryInterface $userRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private StateIconAndTitle $stateIconAndTitle;

    private SystemDataDeserializerInterface $systemDataDeserializer;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->colonyLibFactory = $this->mock(ColonyLibFactoryInterface::class);
        $this->torpedoTypeRepository = $this->mock(TorpedoTypeRepositoryInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->shipStateChanger = $this->mock(ShipStateChangerInterface::class);
        $this->repairUtil = $this->mock(RepairUtilInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->stateIconAndTitle = $this->mock(StateIconAndTitle::class);
        $this->systemDataDeserializer = $this->mock(SystemDataDeserializerInterface::class);

        $this->shipWrapperFactory = new ShipWrapperFactory(
            $this->shipSystemManager,
            $this->colonyLibFactory,
            $this->torpedoTypeRepository,
            $this->game,
            $this->shipStateChanger,
            $this->repairUtil,
            $this->userRepository,
            $this->stateIconAndTitle,
            $this->systemDataDeserializer
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

        $fallbackUser = $this->mock(UserInterface::class);

        $this->userRepository->shouldReceive('getFallbackUser')
            ->withNoArgs()
            ->once()
            ->andReturn($fallbackUser);
        $fallbackUser->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $fleetwrapper = $this->shipWrapperFactory->wrapShipsAsFleet($shipArray, true);

        $this->assertEquals(2, count($fleetwrapper->getShipWrappers()));
        $this->assertEquals($shipA, $fleetwrapper->getShipWrappers()[12]->get());
        $this->assertEquals($shipB, $fleetwrapper->getShipWrappers()[27]->get());
        $this->assertEquals('Einzelschiffe', $fleetwrapper->get()->getName());
        $this->assertEquals(PHP_INT_MAX, $fleetwrapper->get()->getSort());
        $this->assertEquals(42, $fleetwrapper->get()->getUser()->getId());
    }

    public function testWrapShipsAsFleetIfNotSingleShipMode(): void
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
            ->once()
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
