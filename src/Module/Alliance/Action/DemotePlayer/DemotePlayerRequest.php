<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DemotePlayer;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DemotePlayerRequest implements DemotePlayerRequestInterface
{
    use CustomControllerHelperTrait;

    public function getPlayerId(): int
    {
        return $this->queryParameter('uid')->int()->required();
    }
}
