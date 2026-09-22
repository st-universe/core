<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Mockery;
use request;
use Stu\Component\Game\GameStateEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Game\RedirectionException;
use Stu\Exception\MaintenanceGameStateException;
use Stu\Lib\Session\SessionInterface;
use Stu\Module\Control\Component\CallbackExecutionInterface;
use Stu\StuTestCase;

class MaintenanceLoginExecutorTest extends StuTestCase
{
    public function testExecuteIfRequiredReturnsFalseForNonIndexModule(): void
    {
        $callbackExecution = $this->mock(CallbackExecutionInterface::class);
        $gameState = $this->mock(GameStateInterface::class);
        $session = $this->mock(SessionInterface::class);
        $roleChecker = $this->mock(GameUserRoleCheckerInterface::class);
        $game = $this->mock(GameControllerInterface::class);

        $callbackExecution->shouldNotReceive('execute');
        $gameState->shouldNotReceive('getGameState');
        $session->shouldNotReceive('logout');

        $result = (new MaintenanceLoginExecutor($callbackExecution, $gameState, $session, $roleChecker))
            ->executeIfRequired(ModuleEnum::GAME, $game);

        $this->assertFalse($result);
    }

    public function testExecuteIfRequiredExecutesLoginDuringMaintenance(): void
    {
        request::setMockVars(['B_LOGIN' => true]);
        $callbackExecution = $this->mock(CallbackExecutionInterface::class);
        $gameState = $this->mock(GameStateInterface::class);
        $session = $this->mock(SessionInterface::class);
        $roleChecker = $this->mock(GameUserRoleCheckerInterface::class);
        $game = $this->mock(GameControllerInterface::class);

        $gameState->shouldReceive('getGameState')->once()->andReturn(GameStateEnum::MAINTENANCE);
        $callbackExecution->shouldReceive('execute')->with(ModuleEnum::INDEX, $game)->once();

        $result = (new MaintenanceLoginExecutor($callbackExecution, $gameState, $session, $roleChecker))
            ->executeIfRequired(ModuleEnum::INDEX, $game);

        $this->assertTrue($result);
    }

    public function testExecuteIfRequiredConvertsNonAdminRedirection(): void
    {
        request::setMockVars(['B_LOGIN' => true]);
        $callbackExecution = $this->mock(CallbackExecutionInterface::class);
        $gameState = $this->mock(GameStateInterface::class);
        $session = $this->mock(SessionInterface::class);
        $roleChecker = $this->mock(GameUserRoleCheckerInterface::class);
        $game = $this->mock(GameControllerInterface::class);

        $gameState->shouldReceive('getGameState')->once()->andReturn(GameStateEnum::MAINTENANCE);
        $callbackExecution->shouldReceive('execute')
            ->with(ModuleEnum::INDEX, $game)
            ->once()
            ->andThrow(new RedirectionException('/login'));
        $roleChecker->shouldReceive('isAdmin')->once()->andReturnFalse();
        $session->shouldReceive('logout')->once();

        static::expectException(MaintenanceGameStateException::class);

        (new MaintenanceLoginExecutor($callbackExecution, $gameState, $session, $roleChecker))
            ->executeIfRequired(ModuleEnum::INDEX, $game);
    }
}
