<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowShoutBox;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowShoutBoxRequest implements ShowShoutBoxRequestInterface
{
    use CustomControllerHelperTrait;

    public function getTradeNetworkId(): int
    {
        return $this->queryParameter('network')->int()->required();
    }
}
