<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\TransferGoods;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class TransferGoodsRequest implements TransferGoodsRequestInterface
{
    use CustomControllerHelperTrait;

    public function getStorageId(): int
    {
        return $this->queryParameter('storid')->int()->required();
    }

    public function getAmount(): int
    {
        return $this->queryParameter('count')->int()->required();
    }

    public function getDestinationTradePostId(): int
    {
        return $this->queryParameter('target')->int()->required();
    }

}