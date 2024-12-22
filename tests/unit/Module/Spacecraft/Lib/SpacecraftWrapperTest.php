<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\Data\HullSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SystemDataDeserializerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\Ui\StateIconAndTitle;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapper;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\StuTestCase;

class SpacecraftWrapperTest extends StuTestCase
{
    /** @var MockInterface&ShipInterface */
    private  $spacecraft;
    /** @var MockInterface&SpacecraftSystemManagerInterface */
    private  $spacecraftSystemManager;
    /** @var MockInterface&SystemDataDeserializerInterface */
    private  $systemDataDeserializer;
    /** @var MockInterface&TorpedoTypeRepositoryInterface */
    private  $torpedoTypeRepository;
    /** @var MockInterface&GameControllerInterface */
    private  $game;
    /** @var MockInterface&SpacecraftWrapperFactoryInterface */
    private  $spacecraftWrapperFactory;
    /** @var MockInterface&SpacecraftStateChangerInterface */
    private  $spacecraftStateChanger;
    /** @var MockInterface&RepairUtilInterface */
    private  $repairUtil;
    /** @var MockInterface&StateIconAndTitle */
    private  $stateIconAndTitle;

    private SpacecraftWrapperInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->spacecraft = $this->mock(ShipInterface::class);
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->systemDataDeserializer = $this->mock(SystemDataDeserializerInterface::class);
        $this->torpedoTypeRepository = $this->mock(TorpedoTypeRepositoryInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->spacecraftWrapperFactory = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->spacecraftStateChanger = $this->mock(SpacecraftStateChangerInterface::class);
        $this->repairUtil = $this->mock(RepairUtilInterface::class);
        $this->stateIconAndTitle = $this->mock(StateIconAndTitle::class);

        $this->subject = new class(
            $this->spacecraft,
            $this->spacecraftSystemManager,
            $this->systemDataDeserializer,
            $this->torpedoTypeRepository,
            $this->game,
            $this->spacecraftWrapperFactory,
            $this->spacecraftStateChanger,
            $this->repairUtil,
            $this->stateIconAndTitle
        ) extends SpacecraftWrapper {
            #[Override]
            public function getFleetWrapper(): ?FleetWrapperInterface
            {
                return null;
            }
        };
    }

    public function testGetHullSystemData(): void
    {
        $hullSystemData = $this->mock(HullSystemData::class);

        $this->systemDataDeserializer->shouldReceive('getSpecificShipSystem')
            ->with(
                $this->spacecraft,
                SpacecraftSystemTypeEnum::HULL,
                HullSystemData::class,
                Mockery::any(),
                $this->spacecraftWrapperFactory
            )
            ->once()
            ->andReturn($hullSystemData);

        $hull = $this->subject->getHullSystemData();

        $this->assertEquals($hullSystemData, $hull);
    }


    public function testCanFireExpectFalseWhenNbsOffline(): void
    {
        $this->spacecraft->shouldReceive('getNbs')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->subject->canFire();

        $this->assertFalse($result);
    }

    public function testCanFireExpectFalseWhenWeaponsOffline(): void
    {
        $this->spacecraft->shouldReceive('getNbs')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->spacecraft->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->subject->canFire();

        $this->assertFalse($result);
    }

    public function testCanFireExpectFalseWhenNoEpsInstalled(): void
    {
        $this->spacecraft->shouldReceive('getNbs')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->spacecraft->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->systemDataDeserializer->shouldReceive('getSpecificShipSystem')
            ->with(
                $this->spacecraft,
                SpacecraftSystemTypeEnum::EPS,
                EpsSystemData::class,
                Mockery::any(),
                $this->spacecraftWrapperFactory
            )
            ->once()
            ->andReturn(null);

        $result = $this->subject->canFire();

        $this->assertFalse($result);
    }

    public function testCanFireExpectTrueWhenEverythingIsFine(): void
    {
        $epsSystemData = $this->mock(EpsSystemData::class);

        $this->spacecraft->shouldReceive('getNbs')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->spacecraft->shouldReceive('hasActiveWeapon')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->systemDataDeserializer->shouldReceive('getSpecificShipSystem')
            ->with(
                $this->spacecraft,
                SpacecraftSystemTypeEnum::EPS,
                EpsSystemData::class,
                Mockery::any(),
                $this->spacecraftWrapperFactory
            )
            ->once()
            ->andReturn($epsSystemData);

        $epsSystemData->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $result = $this->subject->canFire();

        $this->assertTrue($result);
    }
}
