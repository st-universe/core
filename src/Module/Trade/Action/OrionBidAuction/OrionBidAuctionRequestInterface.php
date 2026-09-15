<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\OrionBidAuction;

interface OrionBidAuctionRequestInterface
{
    public function getAuctionId(): int;

    public function getAmount(): int;
}
