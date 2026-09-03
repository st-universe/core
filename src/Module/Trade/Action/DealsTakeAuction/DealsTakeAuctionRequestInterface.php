<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsTakeAuction;

interface DealsTakeAuctionRequestInterface
{
    public function getDealId(): int;
}
