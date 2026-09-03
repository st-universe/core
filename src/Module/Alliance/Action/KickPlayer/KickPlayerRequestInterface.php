<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\KickPlayer;

interface KickPlayerRequestInterface
{
    public function getPlayerId(): int;
}
