<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Ui;

use JBBCode\Parser;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\Data\AstroLaboratorySystemData;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipTakeover;
use Stu\StuTestCase;

class StateIconAndTitleTest extends StuTestCase
{
    private MockInterface&GameControllerInterface $game;
    private MockInterface&Parser $bbCodeParser;

    private MockInterface&ShipWrapperInterface $wrapper;
    private MockInterface&Ship $ship;

    private StateIconAndTitle $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->game = $this->mock(GameControllerInterface::class);
        $this->bbCodeParser = $this->mock(Parser::class);

        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(Ship::class);

        $this->wrapper->shouldReceive('get')
            ->andReturn($this->ship);

        $this->subject = new StateIconAndTitle(
            $this->game,
            $this->bbCodeParser
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

    public static function getStateIconAndTitleForPassiveRepairProvider(): array
    {
        return [
            [false, "Schiff wird repariert (noch 42 Runden)"],
            [true, "Station wird repariert (noch 42 Runden)"],
        ];
    }

    #[DataProvider('getStateIconAndTitleForPassiveRepairProvider')]
    public function testGetStateIconAndTitleForPassiveRepair(bool $isStation, string $expectedTitle): void
    {
        $this->ship->shouldReceive('getState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftStateEnum::REPAIR_PASSIVE);
        $this->ship->shouldReceive('isStation')
            ->withNoArgs()
            ->once()
            ->andReturn($isStation);

        $this->wrapper->shouldReceive('getRepairDuration')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        [$icon, $title] = $this->subject->getStateIconAndTitle($this->wrapper);

        $this->assertEquals('buttons/rep2', $icon);
        $this->assertEquals($expectedTitle, $title);
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
