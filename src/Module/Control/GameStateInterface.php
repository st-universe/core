<?php

namespace Stu\Module\Control;

use Stu\Component\Game\GameStateEnum;

interface GameStateInterface
{
    public const int CONFIG_GAMESTATE = 1;

    public function getGameState(): GameStateEnum;

    public function checkGameState(bool $isAdmin): void;
}
