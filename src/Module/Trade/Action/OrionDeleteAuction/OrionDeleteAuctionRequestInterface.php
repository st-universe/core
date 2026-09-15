<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\OrionDeleteAuction;

interface OrionDeleteAuctionRequestInterface
{
    public function getAuctionId(): int;
}
