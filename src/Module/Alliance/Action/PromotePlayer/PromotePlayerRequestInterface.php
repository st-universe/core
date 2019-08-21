<?php

namespace Stu\Module\Alliance\Action\PromotePlayer;

interface PromotePlayerRequestInterface
{
    public function getPlayerId(): int;

    public function getPromotionType(): int;
}