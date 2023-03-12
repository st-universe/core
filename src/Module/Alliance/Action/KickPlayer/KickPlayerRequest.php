<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\KickPlayer;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class KickPlayerRequest implements KickPlayerRequestInterface
{
    use CustomControllerHelperTrait;

    public function getPlayerId(): int
    {
        return $this->queryParameter('uid')->int()->required();
    }
}