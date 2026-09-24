<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib;

use Stu\Orm\Entity\GameTurn;

interface GameTurnProviderInterface {

    public function getCurrentRound(): GameTurn;
}
