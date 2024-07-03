<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsBidAuction;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class DealsBidAuctionRequest implements DealsBidAuctionRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getDealId(): int
    {
        return $this->queryParameter('dealid')->int()->required();
    }

    #[Override]
    public function getMaxAmount(): int
    {
        return $this->queryParameter('maxamount')->int()->defaultsTo(0);
    }
}
