<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use JBBCode\Parser;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Ship\Repair\RepairUtilInterface;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Data\AstroLaboratorySystemData;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\Data\HullSystemData;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\SystemDataDeserializerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipTakeoverInterface;
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

    private Parser $bbCodeParser;

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
        $this->bbCodeParser = $this->mock(Parser::class);

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
            $this->bbCodeParser
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

    public static function getStateIconAndTitleForActiveRepairProvider(): array
    {
        return [
            [false, "Schiffscrew repariert die Station"],
            [true, "Stationscrew repariert die Station"],
        ];
    }

    #[DataProvider('getStateIconAndTitleForActiveRepairProvider')]
    public function testGetStateIconAndTitleForActiveRepair(bool $isBase, string $expectedTitle): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_REPAIR_ACTIVE);
        $this->ship->shouldReceive('isBase')
            ->withNoArgs()
            ->once()
            ->andReturn($isBase);

        [$icon, $title] = $this->shipWrapper->getStateIconAndTitle();

        $this->assertEquals('rep2', $icon);
        $this->assertEquals($expectedTitle, $title);
    }

    public static function getStateIconAndTitleForPassiveRepairProvider(): array
    {
        return [
            [false, "Schiff wird repariert (noch 42 Runden)"],
            [true, "Station wird repariert (noch 42 Runden)"],
        ];
    }

    #[DataProvider('getStateIconAndTitleForPassiveRepairProvider')]
    public function testGetStateIconAndTitleForPassiveRepair(bool $isBase, string $expectedTitle): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_REPAIR_PASSIVE);
        $this->ship->shouldReceive('isBase')
            ->withNoArgs()
            ->once()
            ->andReturn($isBase);

        $this->repairUtil->shouldReceive('getRepairDuration')
            ->with($this->shipWrapper)
            ->once()
            ->andReturn(42);

        [$icon, $title] = $this->shipWrapper->getStateIconAndTitle();

        $this->assertEquals('rep2', $icon);
        $this->assertEquals($expectedTitle, $title);
    }

    public function testGetStateIconAndTitleForAstroFinalizing(): void
    {
        $astroLab = $this->mock(AstroLaboratorySystemData::class);

        $this->systemDataDeserializer->shouldReceive('getSpecificShipSystem')
            ->with(
                $this->ship,
                ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY,
                AstroLaboratorySystemData::class,
                Mockery::any(),
                $this->shipWrapperFactory
            )
            ->once()
            ->andReturn($astroLab);

        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING);
        $astroLab->shouldReceive('getAstroStartTurn')
            ->withNoArgs()
            ->once()
            ->andReturn(5);



        $this->game->shouldReceive('getCurrentRound->getTurn')
            ->withNoArgs()
            ->once()
            ->andReturn(6);

        [$icon, $title] = $this->shipWrapper->getStateIconAndTitle();

        $this->assertEquals('map1', $icon);
        $this->assertEquals('Schiff kartographiert (noch 2 Runden)', $title);
    }

    public function testGetStateIconAndTitleForActiveTakeover(): void
    {
        $takeover = $this->mock(ShipTakeoverInterface::class);

        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_ACTIVE_TAKEOVER);
        $this->ship->shouldReceive('getTakeoverActive')
            ->withNoArgs()
            ->once()
            ->andReturn($takeover);

        $takeover->shouldReceive('getTargetShip->getName')
            ->withNoArgs()
            ->once()
            ->andReturn('BBCODENAME');
        $takeover->shouldReceive('getStartTurn')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $this->bbCodeParser->shouldReceive('parse')
            ->with('BBCODENAME')
            ->once()
            ->andReturnSelf();
        $this->bbCodeParser->shouldReceive('getAsText')
            ->withNoArgs()
            ->once()
            ->andReturn('TARGET');
        $this->game->shouldReceive('getCurrentRound->getTurn')
            ->withNoArgs()
            ->andReturn(6);

        $this->systemDataDeserializer->shouldReceive('getSpecificShipSystem')
            ->with(
                $this->ship,
                ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY,
                AstroLaboratorySystemData::class,
                Mockery::any(),
                $this->shipWrapperFactory
            )
            ->once()
            ->andReturn(null);

        [$icon, $title] = $this->shipWrapper->getStateIconAndTitle();

        $this->assertEquals('take2', $icon);
        $this->assertEquals('Schiff übernimmt die "TARGET" (noch 9 Runden)', $title);
    }

    public function testGetStateIconAndTitleForPassiveTakeover(): void
    {
        $takeover = $this->mock(ShipTakeoverInterface::class);
        $astroLab = $this->mock(AstroLaboratorySystemData::class);

        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_NONE);
        $this->ship->shouldReceive('getTakeoverActive')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $this->ship->shouldReceive('getTakeoverPassive')
            ->withNoArgs()
            ->once()
            ->andReturn($takeover);

        $takeover->shouldReceive('getSourceShip->getUser->getName')
            ->withNoArgs()
            ->once()
            ->andReturn('BBCODENAME');
        $takeover->shouldReceive('getStartTurn')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $this->bbCodeParser->shouldReceive('parse')
            ->with('BBCODENAME')
            ->once()
            ->andReturnSelf();
        $this->bbCodeParser->shouldReceive('getAsText')
            ->withNoArgs()
            ->once()
            ->andReturn('USER');
        $this->game->shouldReceive('getCurrentRound->getTurn')
            ->withNoArgs()
            ->andReturn(6);


        $this->systemDataDeserializer->shouldReceive('getSpecificShipSystem')
            ->with(
                $this->ship,
                ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY,
                AstroLaboratorySystemData::class,
                Mockery::any(),
                $this->shipWrapperFactory
            )
            ->once()
            ->andReturn($astroLab);

        [$icon, $title] = $this->shipWrapper->getStateIconAndTitle();

        $this->assertEquals('untake2', $icon);
        $this->assertEquals('Schiff wird von Spieler "USER" übernommen (noch 9 Runden)', $title);
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
