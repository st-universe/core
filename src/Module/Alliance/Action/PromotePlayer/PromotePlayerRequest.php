<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\PromotePlayer;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class PromotePlayerRequest implements PromotePlayerRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getPlayerId(): int
    {
        return $this->parameter('uid')->int()->required();
    }

    #[\Override]
    public function getPromotionType(): int
    {
        return $this->parameter('type')->int()->required();
    }
}
