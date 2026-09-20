<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Orm\Entity\GameRequest;

interface GameResponseFinalizerInterface
{
    public function finalize(GameControllerInterface $game, GameRequest $gameRequest): string;
}
