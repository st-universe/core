<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\TransferCommodities;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class TransferCommoditiesRequest implements TransferCommoditiesRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getStorageId(): int
    {
        return $this->queryParameter('storid')->int()->required();
    }

    #[Override]
    public function getAmount(): int
    {
        return $this->queryParameter('count')->int()->defaultsTo(0);
    }

    #[Override]
    public function getDestinationTradePostId(): int
    {
        return $this->queryParameter('target')->int()->defaultsTo(-1);
    }
}
