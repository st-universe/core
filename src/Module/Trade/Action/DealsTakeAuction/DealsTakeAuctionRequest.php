<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsTakeAuction;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DealsTakeAuctionRequest implements DealsTakeAuctionRequestInterface
{
    use CustomControllerHelperTrait;

    public function getDealId(): int
    {
        return $this->queryParameter('dealid')->int()->required();
    }
}
