<?php

declare(strict_types=1);

namespace Stu\Config;

use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\GameControllerInterface;

final class GameRequestRunner implements GameRequestRunnerInterface
{
    public function __construct(
        private readonly GameControllerInterface $gameController,
        private readonly SessionStarterInterface $sessionStarter
    ) {}

    #[\Override]
    public function run(ModuleEnum $module): void
    {
        $this->sessionStarter->start();

        $this->gameController->main($module);
    }
}
