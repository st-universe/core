<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\TransferCommodities;

interface TransferCommoditiesRequestInterface
{
    public function getStorageId(): int;

    public function getAmount(): int;

    public function getDestinationTradePostId(): int;
}
