<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Mockery\MockInterface;
use request;
use Stu\Component\Game\GameStateEnum;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\GameStateInterface;
use Stu\Orm\Entity\GameConfig;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\StuTestCase;

class SetGameStateTest extends StuTestCase
{
    private MockInterface&GameConfigRepositoryInterface $gameConfigRepository;

    private MockInterface&GameControllerInterface $game;

    private SetGameState $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->gameConfigRepository = $this->mock(GameConfigRepositoryInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);

        $this->subject = new SetGameState(
            $this->gameConfigRepository
        );
    }

    public function testHandleDoesNothingWhenNotAdmin(): void
    {
        $this->game->shouldReceive('setView')
            ->with(ShowScripts::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->game->shouldReceive('getInfo->addInformation')
            ->with('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]')
            ->once();

        $this->subject->handle($this->game);
    }

    public function testHandleRejectsInvalidGameState(): void
    {
        request::setMockVars(['game_state' => 999]);

        $this->game->shouldReceive('setView')
            ->with(ShowScripts::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->game->shouldReceive('getInfo->addInformation')
            ->with('Ungültiger Spielmodus')
            ->once();

        $this->subject->handle($this->game);
    }

    public function testHandleRejectsMissingGameStateConfig(): void
    {
        request::setMockVars(['game_state' => GameStateEnum::MAINTENANCE->value]);

        $this->game->shouldReceive('setView')
            ->with(ShowScripts::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->gameConfigRepository->shouldReceive('getByOption')
            ->with(GameStateInterface::CONFIG_GAMESTATE)
            ->once()
            ->andReturn(null);
        $this->game->shouldReceive('getInfo->addInformation')
            ->with('Spielmodus-Konfiguration nicht gefunden')
            ->once();

        $this->subject->handle($this->game);
    }

    public function testHandleUpdatesGameState(): void
    {
        request::setMockVars(['game_state' => GameStateEnum::RELOCATION->value]);

        $gameConfig = new GameConfig();

        $this->game->shouldReceive('setView')
            ->with(ShowScripts::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $this->gameConfigRepository->shouldReceive('getByOption')
            ->with(GameStateInterface::CONFIG_GAMESTATE)
            ->once()
            ->andReturn($gameConfig);
        $this->gameConfigRepository->shouldReceive('save')
            ->with($gameConfig)
            ->once();
        $this->game->shouldReceive('getInfo->addInformation')
            ->with('Der Spielmodus wurde auf "Umzug" gesetzt')
            ->once();

        $this->subject->handle($this->game);

        static::assertSame(GameStateEnum::RELOCATION->value, $gameConfig->getValue());
    }

    public function testPerformSessionCheckReturnsTrue(): void
    {
        static::assertTrue($this->subject->performSessionCheck());
    }
}
