<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsBidAuction;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DealsBidAuctionRequest implements DealsBidAuctionRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getDealId(): int
    {
        return $this->parameter('dealid')->int()->required();
    }

    #[\Override]
    public function getMaxAmount(): int
    {
        return $this->parameter('maxamount')->int()->defaultsTo(0);
    }
}
