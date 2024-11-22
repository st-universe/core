<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowTradePostInfo;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowTradePostInfoRequest implements ShowTradePostInfoRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getTradePostId(): int
    {
        return $this->parameter('postid')->int()->required();
    }
}
