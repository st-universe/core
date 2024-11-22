<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DemotePlayer;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class DemotePlayerRequest implements DemotePlayerRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getPlayerId(): int
    {
        return $this->parameter('uid')->int()->required();
    }
}
