<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Scripts;

use Mockery\MockInterface;
use Stu\Component\Game\GameStateEnum;
use Stu\Component\Map\MapEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\GameStateInterface;
use Stu\Orm\Repository\PirateSetupRepositoryInterface;
use Stu\StuTestCase;

class ShowScriptsTest extends StuTestCase
{
    private MockInterface&GameStateInterface $gameState;

    private MockInterface&PirateSetupRepositoryInterface $pirateSetupRepository;

    private ShowScripts $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->gameState = $this->mock(GameStateInterface::class);
        $this->pirateSetupRepository = $this->mock(PirateSetupRepositoryInterface::class);

        $this->subject = new ShowScripts(
            $this->gameState,
            $this->pirateSetupRepository
        );
    }

    public function testHandleSetsTemplateVars(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $pirateSetups = [];

        $game->shouldReceive('setTemplateFile')
            ->with('html/admin/scripts.twig')
            ->once();
        $game->shouldReceive('appendNavigationPart')
            ->with('/admin/?SHOW_SCRIPTS=1', 'Scripts')
            ->once();
        $game->shouldReceive('setPageTitle')
            ->with('Scripts')
            ->once();
        $this->pirateSetupRepository->shouldReceive('getAllOrderedByName')
            ->withNoArgs()
            ->once()
            ->andReturn($pirateSetups);
        $this->gameState->shouldReceive('getGameState')
            ->withNoArgs()
            ->once()
            ->andReturn(GameStateEnum::MAINTENANCE);
        $game->shouldReceive('setTemplateVar')
            ->with('DEFAULT_LAYER', MapEnum::DEFAULT_LAYER)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('PIRATE_SETUPS', $pirateSetups)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('CURRENT_GAME_STATE', GameStateEnum::MAINTENANCE)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('GAME_STATES', GameStateEnum::cases())
            ->once();

        $this->subject->handle($game);
    }
}
