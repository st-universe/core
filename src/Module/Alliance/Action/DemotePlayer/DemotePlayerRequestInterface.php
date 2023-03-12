<?php

namespace Stu\Module\Alliance\Action\DemotePlayer;

interface DemotePlayerRequestInterface
{
    public function getPlayerId(): int;
}