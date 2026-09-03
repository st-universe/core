<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsBidAuction;

interface DealsBidAuctionRequestInterface
{
    public function getDealId(): int;

    public function getMaxAmount(): int;
}
