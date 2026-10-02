<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\GameTurn;
use Stu\Orm\Entity\User;

interface GameControllerInterface
{
    public function getGameData(): GameData;

    public function getUser(): User;

    public function hasUser(): bool;

    public function getGameRequest(): GameRequest;

    public function main(ModuleEnum $view, GameRequest $gameRequest): void;

    public function getInfo(): InformationWrapper;

    public function getCurrentRound(): GameTurn;

    public function resetGameData(): void;
}
