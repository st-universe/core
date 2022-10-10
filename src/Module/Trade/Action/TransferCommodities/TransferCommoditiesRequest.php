<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\TransferCommodities;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class TransferCommoditiesRequest implements TransferCommoditiesRequestInterface
{
    use CustomControllerHelperTrait;

    public function getStorageId(): int
    {
        return $this->queryParameter('storid')->int()->required();
    }

    public function getAmount(): int
    {
        return $this->queryParameter('count')->int()->defaultsTo(0);
    }

    public function getDestinationTradePostId(): int
    {
        return $this->queryParameter('target')->int()->defaultsTo(-1);
    }
}
