<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DemotePlayer;

interface DemotePlayerRequestInterface
{
    public function getPlayerId(): int;
}
