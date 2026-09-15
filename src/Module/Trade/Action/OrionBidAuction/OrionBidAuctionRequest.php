<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\OrionBidAuction;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class OrionBidAuctionRequest implements OrionBidAuctionRequestInterface
{
    use CustomControllerHelperTrait;

    public function getAuctionId(): int
    {
        return $this->parameter('auctionid')->int()->defaultsTo(0);
    }

    public function getAmount(): int
    {
        return $this->parameter('amount')->int()->defaultsTo(0);
    }
}
