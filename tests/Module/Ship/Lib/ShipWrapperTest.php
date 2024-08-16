<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Component\Ship\Repair\RepairUtilInterface;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\Data\HullSystemData;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\SystemDataDeserializerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Ui\StateIconAndTitle;
use Stu\Orm\Entity\ShipInterface;
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

    private SystemDataDeserializerInterface $systemDataDeserializer;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private GameControllerInterface $game;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private ShipStateChangerInterface $shipStateChanger;

    private RepairUtilInterface $repairUtil;

    private ShipWrapper $shipWrapper;

    private StateIconAndTitle $stateIconAndTitle;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->ship = $this->mock(ShipInterface::class);
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->systemDataDeserializer = $this->mock(SystemDataDeserializerInterface::class);
        $this->colonyLibFactory = $this->mock(ColonyLibFactoryInterface::class);
        $this->torpedoTypeRepository = $this->mock(TorpedoTypeRepositoryInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->shipWrapperFactory = $this->mock(ShipWrapperFactoryInterface::class);
        $this->shipStateChanger = $this->mock(ShipStateChangerInterface::class);
        $this->repairUtil = $this->mock(RepairUtilInterface::class);
        $this->stateIconAndTitle = $this->mock(StateIconAndTitle::class);

        $this->shipWrapper = new ShipWrapper(
            $this->ship,
            $this->shipSystemManager,
            $this->systemDataDeserializer,
            $this->colonyLibFactory,
            $this->torpedoTypeRepository,
            $this->game,
            $this->shipWrapperFactory,
            $this->shipStateChanger,
            $this->repairUtil,
            $this->stateIconAndTitle
        );
    }

    public function testGetHullSystemData(): void
    {
        $hullSystemData = $this->mock(HullSystemData::class);

        $this->systemDataDeserializer->shouldReceive('getSpecificShipSystem')
            ->with(
                $this->ship,
                ShipSystemTypeEnum::SYSTEM_HULL,
                HullSystemData::class,
                Mockery::any(),
                $this->shipWrapperFactory
            )
            ->once()
            ->andReturn($hullSystemData);

        $hull = $this->shipWrapper->getHullSystemData();

        $this->assertEquals($hullSystemData, $hull);
    }


    public function testCanFireExpectFalseWhenNbsOffline(): void
    {
        $this->ship->shouldReceive('getNbs')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->shipWrapper->canFire();

        $this->assertFalse($result);
    }

    public function testCanFireExpectFalseWhenWeaponsOffline(): void
    {
        $this->ship->shouldReceive('getNbs')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->shipWrapper->canFire();

        $this->assertFalse($result);
    }

    public function testCanFireExpectFalseWhenNoEpsInstalled(): void
    {
        $this->ship->shouldReceive('getNbs')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->systemDataDeserializer->shouldReceive('getSpecificShipSystem')
            ->with(
                $this->ship,
                ShipSystemTypeEnum::SYSTEM_EPS,
                EpsSystemData::class,
                Mockery::any(),
                $this->shipWrapperFactory
            )
            ->once()
            ->andReturn(null);

        $result = $this->shipWrapper->canFire();

        $this->assertFalse($result);
    }

    public function testCanFireExpectTrueWhenEverythingIsFine(): void
    {
        $epsSystemData = $this->mock(EpsSystemData::class);

        $this->ship->shouldReceive('getNbs')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->systemDataDeserializer->shouldReceive('getSpecificShipSystem')
            ->with(
                $this->ship,
                ShipSystemTypeEnum::SYSTEM_EPS,
                EpsSystemData::class,
                Mockery::any(),
                $this->shipWrapperFactory
            )
            ->once()
            ->andReturn($epsSystemData);

        $epsSystemData->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $result = $this->shipWrapper->canFire();

        $this->assertTrue($result);
    }
}
