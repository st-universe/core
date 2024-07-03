<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\PromotePlayer;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class PromotePlayerRequest implements PromotePlayerRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getPlayerId(): int
    {
        return $this->queryParameter('uid')->int()->required();
    }

    #[Override]
    public function getPromotionType(): int
    {
        return $this->queryParameter('type')->int()->required();
    }
}
