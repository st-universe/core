<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowShoutBox;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowShoutBoxRequest implements ShowShoutBoxRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getTradeNetworkId(): int
    {
        return $this->parameter('network')->int()->required();
    }
}
