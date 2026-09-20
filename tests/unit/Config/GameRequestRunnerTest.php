<?php

declare(strict_types=1);

namespace Stu\Config;

use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\StuTestCase;

class GameRequestRunnerTest extends StuTestCase
{
    public function testRunStartsSessionAndDelegatesToController(): void
    {
        $gameController = $this->mock(GameControllerInterface::class);
        $sessionStarter = $this->mock(SessionStarterInterface::class);

        $sessionStarter->shouldReceive('start')
            ->withNoArgs()
            ->once();
        $gameController->shouldReceive('main')
            ->with(ModuleEnum::GAME)
            ->once();

        $runner = new GameRequestRunner($gameController, $sessionStarter);

        $runner->run(ModuleEnum::GAME);
    }
}