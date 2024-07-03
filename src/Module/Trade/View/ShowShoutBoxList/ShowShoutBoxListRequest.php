<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowShoutBoxList;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowShoutBoxListRequest implements ShowShoutBoxListRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getTradeNetworkId(): int
    {
        return $this->queryParameter('network')->int()->required();
    }
}
