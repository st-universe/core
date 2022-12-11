<?php

namespace Stu\Module\Trade\Action\DealsTakeAuction;

interface DealsTakeAuctionRequestInterface
{
    public function getDealId(): int;
}