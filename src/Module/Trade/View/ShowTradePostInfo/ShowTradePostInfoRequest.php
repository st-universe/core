<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowTradePostInfo;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowTradePostInfoRequest implements ShowTradePostInfoRequestInterface
{
    use CustomControllerHelperTrait;

    public function getTradePostId(): int
    {
        return $this->queryParameter('postid')->int()->required();
    }
}