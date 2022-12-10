<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsBidAuction;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DealsBidAuctionRequest implements DealsBidAuctionRequestInterface
{
    use CustomControllerHelperTrait;

    public function getDealId(): int
    {
        return $this->queryParameter('dealid')->int()->required();
    }

    public function getAmount(): int
    {
        return $this->queryParameter('amount')->int()->defaultsTo(0);
    }

    public function getMaxAmount(): int
    {
        return $this->queryParameter('maxamount')->int()->defaultsTo(0);
    }
}