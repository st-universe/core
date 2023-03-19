<?php

namespace Stu\Module\Alliance\Action\KickPlayer;

interface KickPlayerRequestInterface
{
    public function getPlayerId(): int;
}
