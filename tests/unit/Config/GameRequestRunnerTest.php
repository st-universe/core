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
        $gameController->shouldReceive('main')
            ->with(ModuleEnum::GAME)
            ->once();

        $runner = new GameRequestRunner($gameController);

        $runner->run(ModuleEnum::GAME);
    }
}