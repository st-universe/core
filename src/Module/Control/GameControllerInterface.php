<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Orm\Entity\GameRequest;

interface GameControllerInterface extends ViewControllerContext
{
    public function getGameData(): GameData;

    public function hasUser(): bool;

    public function getGameRequest(): GameRequest;

    public function main(ModuleEnum $view, GameRequest $gameRequest): void;

    public function resetGameData(): void;
}
