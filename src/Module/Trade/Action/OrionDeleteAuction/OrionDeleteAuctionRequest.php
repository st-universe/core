<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\OrionDeleteAuction;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class OrionDeleteAuctionRequest implements OrionDeleteAuctionRequestInterface
{
    use CustomControllerHelperTrait;

    public function getAuctionId(): int
    {
        return $this->parameter('auctionid')->int()->defaultsTo(0);
    }
}
