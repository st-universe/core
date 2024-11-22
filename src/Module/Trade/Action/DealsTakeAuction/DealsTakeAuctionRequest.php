<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsTakeAuction;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class DealsTakeAuctionRequest implements DealsTakeAuctionRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getDealId(): int
    {
        return $this->parameter('dealid')->int()->required();
    }
}
