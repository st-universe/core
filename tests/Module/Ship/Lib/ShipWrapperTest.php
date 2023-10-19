<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use JBBCode\Parser;
use JsonMapper\JsonMapperFactory;
use JsonMapper\JsonMapperInterface;
use Mockery\MockInterface;
use Stu\Component\Ship\Repair\RepairUtilInterface;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\Data\HullSystemData;
use Stu\Component\Ship\System\Data\ShipSystemDataFactoryInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Entity\ShipTakeoverInterface;
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

    private ColonyLibFactoryInterface $colonyLibFactory;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private GameControllerInterface $game;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private ShipSystemDataFactoryInterface $shipSystemDataFactory;

    private ShipStateChangerInterface $shipStateChanger;

    private RepairUtilInterface $repairUtil;

    private JsonMapperInterface $jsonMapper;

    private ShipWrapper $shipWrapper;

    private ShipSystemInterface $shipSystem;

    private Parser $bbCodeParser;

    public function setUp(): void
    {
        //injected
        $this->ship = $this->mock(ShipInterface::class);
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->colonyLibFactory = $this->mock(ColonyLibFactoryInterface::class);
        $this->torpedoTypeRepository = $this->mock(TorpedoTypeRepositoryInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->jsonMapper = (new JsonMapperFactory())->bestFit();
        $this->shipWrapperFactory = $this->mock(ShipWrapperFactoryInterface::class);
        $this->shipSystemDataFactory = $this->mock(ShipSystemDataFactoryInterface::class);
        $this->shipStateChanger = $this->mock(ShipStateChangerInterface::class);
        $this->shipSystem = $this->mock(ShipSystemInterface::class);
        $this->repairUtil = $this->mock(RepairUtilInterface::class);
        $this->bbCodeParser = $this->mock(Parser::class);

        $this->shipWrapper = new ShipWrapper(
            $this->ship,
            $this->shipSystemManager,
            $this->colonyLibFactory,
            $this->torpedoTypeRepository,
            $this->game,
            $this->jsonMapper,
            $this->shipWrapperFactory,
            $this->shipSystemDataFactory,
            $this->shipStateChanger,
            $this->repairUtil,
            $this->bbCodeParser
        );
    }

    public function testGetHullSystemData(): void
    {
        $hullSystemData = new HullSystemData();

        $this->shipSystemDataFactory->shouldReceive('createSystemData')
            ->with(ShipSystemTypeEnum::SYSTEM_HULL, $this->shipWrapperFactory)
            ->once()
            ->andReturn($hullSystemData);

        $hull = $this->shipWrapper->getHullSystemData();

        $this->assertEquals($hullSystemData, $hull);
    }

    public function testGetEpsSystemDataReturnNullIfSystemNotFound(): void
    {
        $this->ship->shouldReceive('hasShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_EPS)
            ->once()
            ->andReturn(false);

        $eps = $this->shipWrapper->getEpsSystemData();

        $this->assertNull($eps);
    }

    public function testGetEpsSystemDataWithDataEmptyExpectDefaultValues(): void
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

    public function testGetEpsSystemDataWithDataNotEmptyExpectCorrectValues(): void
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

    public static function getStateIconAndTitleForActiveRepairProvider()
    {
        return [
            [false, "Schiffscrew repariert die Station"],
            [true, "Stationscrew repariert die Station"],
        ];
    }

    /**
     * @dataProvider getStateIconAndTitleForActiveRepairProvider
     */
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

    public static function getStateIconAndTitleForPassiveRepairProvider()
    {
        return [
            [false, "Schiff wird repariert (noch 42 Runden)"],
            [true, "Station wird repariert (noch 42 Runden)"],
        ];
    }

    /**
     * @dataProvider getStateIconAndTitleForPassiveRepairProvider
     */
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
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING);
        $this->ship->shouldReceive('getAstroStartTurn')
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

        [$icon, $title] = $this->shipWrapper->getStateIconAndTitle();

        $this->assertEquals('take2', $icon);
        $this->assertEquals('Schiff übernimmt die "TARGET" (noch 9 Runden)', $title);
    }

    public function testGetStateIconAndTitleForPassiveTakeover(): void
    {
        $takeover = $this->mock(ShipTakeoverInterface::class);

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

        [$icon, $title] = $this->shipWrapper->getStateIconAndTitle();

        $this->assertEquals('untake2', $icon);
        $this->assertEquals('Schiff wird von Spieler "USER" übernommen (noch 9 Runden)', $title);
    }
}
