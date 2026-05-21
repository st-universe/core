<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Ui;

use JBBCode\Parser;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\Data\AstroLaboratorySystemData;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\PassiveRepairProjectionCalculatorInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyShipRepair;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipTakeover;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\StationShipRepair;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;
use Stu\StuTestCase;

class StateIconAndTitleTest extends StuTestCase
{
    private MockInterface&GameControllerInterface $game;
    private MockInterface&Parser $bbCodeParser;
    private MockInterface&ColonyShipRepairRepositoryInterface $colonyShipRepairRepository;
    private MockInterface&StationShipRepairRepositoryInterface $stationShipRepairRepository;
    private MockInterface&ColonyFunctionManagerInterface $colonyFunctionManager;
    private MockInterface&PassiveRepairProjectionCalculatorInterface $passiveRepairProjectionCalculator;
    private MockInterface&StuTime $stuTime;

    private MockInterface&ShipWrapperInterface $wrapper;
    private MockInterface&Ship $ship;

    private StateIconAndTitle $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->game = $this->mock(GameControllerInterface::class);
        $this->bbCodeParser = $this->mock(Parser::class);
        $this->colonyShipRepairRepository = $this->mock(ColonyShipRepairRepositoryInterface::class);
        $this->stationShipRepairRepository = $this->mock(StationShipRepairRepositoryInterface::class);
        $this->colonyFunctionManager = $this->mock(ColonyFunctionManagerInterface::class);
        $this->passiveRepairProjectionCalculator = $this->mock(PassiveRepairProjectionCalculatorInterface::class);
        $this->stuTime = $this->mock(StuTime::class);

        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(Ship::class);

        $this->wrapper->shouldReceive('get')
            ->andReturn($this->ship);

        $this->subject = new StateIconAndTitle(
            $this->game,
            $this->bbCodeParser,
            $this->colonyShipRepairRepository,
            $this->stationShipRepairRepository,
            $this->colonyFunctionManager,
            $this->passiveRepairProjectionCalculator,
            $this->stuTime
        );
    }

    public static function getStateIconAndTitleForActiveRepairProvider(): array
    {
        return [
            [false, "Schiffscrew repariert das Schiff"],
            [true, "Stationscrew repariert die Station"],
        ];
    }

    #[DataProvider('getStateIconAndTitleForActiveRepairProvider')]
    public function testGetStateIconAndTitleForActiveRepair(bool $isStation, string $expectedTitle): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::REPAIR_ACTIVE);
        $this->ship->shouldReceive('isStation')
            ->withNoArgs()
            ->once()
            ->andReturn($isStation);

        [$icon, $title] = $this->subject->getStateIconAndTitle($this->wrapper);

        $this->assertEquals('buttons/rep2', $icon);
        $this->assertEquals($expectedTitle, $title);
    }

    public function testGetStateIconAndTitleForPassiveShipRepairOnColony(): void
    {
        $colonyShipRepair = $this->mock(ColonyShipRepair::class);
        $colony = $this->mock(Colony::class);

        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::REPAIR_PASSIVE);
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);

        $this->colonyShipRepairRepository->shouldReceive('getByShip')
            ->with(42)
            ->once()
            ->andReturn($colonyShipRepair);
        $colonyShipRepair->shouldReceive('getColony')
            ->withNoArgs()
            ->once()
            ->andReturn($colony);
        $this->colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with($colony, BuildingFunctionEnum::REPAIR_SHIPYARD)
            ->once()
            ->andReturnTrue();
        $colonyShipRepair->shouldReceive('getColonyId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $colonyShipRepair->shouldReceive('getFieldId')
            ->withNoArgs()
            ->once()
            ->andReturn(7);
        $this->colonyShipRepairRepository->shouldReceive('getByColonyField')
            ->with(5, 7)
            ->once()
            ->andReturn([$colonyShipRepair]);

        $this->passiveRepairProjectionCalculator->shouldReceive('getPotentialFinishTime')
            ->with([$colonyShipRepair], 2, true, 42)
            ->once()
            ->andReturn(1_724_320_422);
        $this->stuTime->shouldReceive('transformToStuDateTime')
            ->with(1_724_320_422)
            ->once()
            ->andReturn('22.08.2394 09:53');

        $this->stationShipRepairRepository->shouldNotReceive('getByShip');
        $this->wrapper->shouldNotReceive('getRepairDuration');

        [$icon, $title] = $this->subject->getStateIconAndTitle($this->wrapper);

        $this->assertEquals('buttons/rep2', $icon);
        $this->assertEquals(
            'Schiff wird repariert (voraussichtliche Fertigstellung: 22.08.2394 09:53)',
            $title
        );
    }

    public function testGetStateIconAndTitleForPassiveShipRepairOnStation(): void
    {
        $stationShipRepair = $this->mock(StationShipRepair::class);

        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::REPAIR_PASSIVE);
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);

        $this->colonyShipRepairRepository->shouldReceive('getByShip')
            ->with(42)
            ->once()
            ->andReturn(null);
        $this->stationShipRepairRepository->shouldReceive('getByShip')
            ->with(42)
            ->once()
            ->andReturn($stationShipRepair);
        $stationShipRepair->shouldReceive('getStationId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $this->stationShipRepairRepository->shouldReceive('getByStation')
            ->with(5)
            ->once()
            ->andReturn([$stationShipRepair]);

        $this->passiveRepairProjectionCalculator->shouldReceive('getPotentialFinishTime')
            ->with([$stationShipRepair], 1, false, 42)
            ->once()
            ->andReturn(1_724_320_422);
        $this->stuTime->shouldReceive('transformToStuDateTime')
            ->with(1_724_320_422)
            ->once()
            ->andReturn('22.08.2394 09:53');

        $this->wrapper->shouldNotReceive('getRepairDuration');

        [$icon, $title] = $this->subject->getStateIconAndTitle($this->wrapper);

        $this->assertEquals('buttons/rep2', $icon);
        $this->assertEquals(
            'Schiff wird repariert (voraussichtliche Fertigstellung: 22.08.2394 09:53)',
            $title
        );
    }

    public function testGetStateIconAndTitleForPassiveShipRepairWithoutProjectedFinish(): void
    {
        $stationShipRepair = $this->mock(StationShipRepair::class);

        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::REPAIR_PASSIVE);
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);

        $this->colonyShipRepairRepository->shouldReceive('getByShip')
            ->with(42)
            ->once()
            ->andReturn(null);
        $this->stationShipRepairRepository->shouldReceive('getByShip')
            ->with(42)
            ->once()
            ->andReturn($stationShipRepair);
        $stationShipRepair->shouldReceive('getStationId')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $this->stationShipRepairRepository->shouldReceive('getByStation')
            ->with(5)
            ->once()
            ->andReturn([$stationShipRepair]);

        $this->passiveRepairProjectionCalculator->shouldReceive('getPotentialFinishTime')
            ->with([$stationShipRepair], 1, false, 42)
            ->once()
            ->andReturn(0);
        $this->stuTime->shouldNotReceive('transformToStuDateTime');

        $this->wrapper->shouldNotReceive('getRepairDuration');

        [$icon, $title] = $this->subject->getStateIconAndTitle($this->wrapper);

        $this->assertEquals('buttons/rep2', $icon);
        $this->assertEquals('Schiff wird repariert (Fertigstellung ausstehend)', $title);
    }

    public function testGetStateIconAndTitleForPassiveShipRepairWithoutRepairJob(): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::REPAIR_PASSIVE);
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);

        $this->colonyShipRepairRepository->shouldReceive('getByShip')
            ->with(42)
            ->once()
            ->andReturn(null);
        $this->stationShipRepairRepository->shouldReceive('getByShip')
            ->with(42)
            ->once()
            ->andReturn(null);
        $this->passiveRepairProjectionCalculator->shouldNotReceive('getPotentialFinishTime');
        $this->stuTime->shouldNotReceive('transformToStuDateTime');
        $this->wrapper->shouldNotReceive('getRepairDuration');

        [$icon, $title] = $this->subject->getStateIconAndTitle($this->wrapper);

        $this->assertEquals('buttons/rep2', $icon);
        $this->assertEquals('Schiff wird repariert (Fertigstellung ausstehend)', $title);
    }

    public function testGetStateIconAndTitleForPassiveStationRepairFallback(): void
    {
        $stationWrapper = $this->mock(SpacecraftWrapperInterface::class);
        $station = $this->mock(Station::class);

        $stationWrapper->shouldReceive('get')
            ->andReturn($station);
        $station->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::REPAIR_PASSIVE);
        $station->shouldReceive('isStation')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $stationWrapper->shouldReceive('getRepairDuration')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        [$icon, $title] = $this->subject->getStateIconAndTitle($stationWrapper);

        $this->assertEquals('buttons/rep2', $icon);
        $this->assertEquals('Station wird repariert (noch 42 Runden)', $title);
    }

    public function testGetStateIconAndTitleForAstroFinalizing(): void
    {
        $astroLab = $this->mock(AstroLaboratorySystemData::class);

        $this->wrapper->shouldReceive('getAstroLaboratorySystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($astroLab);

        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::ASTRO_FINALIZING);
        $astroLab->shouldReceive('getAstroStartTurn')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $this->game->shouldReceive('getCurrentRound->getTurn')
            ->withNoArgs()
            ->once()
            ->andReturn(6);

        [$icon, $title] = $this->subject->getStateIconAndTitle($this->wrapper);

        $this->assertEquals('buttons/map1', $icon);
        $this->assertEquals('Schiff kartographiert (noch 2 Runden)', $title);
    }

    public function testGetStateIconAndTitleForActiveTakeover(): void
    {
        $takeover = $this->mock(ShipTakeover::class);

        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::ACTIVE_TAKEOVER);
        $this->ship->shouldReceive('getTakeoverActive')
            ->withNoArgs()
            ->once()
            ->andReturn($takeover);

        $takeover->shouldReceive('getTargetSpacecraft->getName')
            ->withNoArgs()
            ->once()
            ->andReturn('BBCODENAME');

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

        $this->wrapper->shouldReceive('getTakeoverTicksLeft')
            ->with($takeover)
            ->once()
            ->andReturn(9);

        [$icon, $title] = $this->subject->getStateIconAndTitle($this->wrapper);

        $this->assertEquals('buttons/take2', $icon);
        $this->assertEquals('Schiff übernimmt die "TARGET" (noch 9 Runden)', $title);
    }

    public function testGetStateIconAndTitleForPassiveTakeover(): void
    {
        $takeover = $this->mock(ShipTakeover::class);

        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::NONE);
        $this->ship->shouldReceive('getTakeoverPassive')
            ->withNoArgs()
            ->once()
            ->andReturn($takeover);

        $takeover->shouldReceive('getSourceSpacecraft->getUser->getName')
            ->withNoArgs()
            ->once()
            ->andReturn('BBCODENAME');

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

        $this->wrapper->shouldReceive('getTakeoverTicksLeft')
            ->with($takeover)
            ->once()
            ->andReturn(9);

        [$icon, $title] = $this->subject->getStateIconAndTitle($this->wrapper);

        $this->assertEquals('buttons/untake2', $icon);
        $this->assertEquals('Schiff wird von Spieler "USER" übernommen (noch 9 Runden)', $title);
    }
}
