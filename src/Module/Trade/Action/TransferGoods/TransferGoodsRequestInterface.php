<?php

namespace Stu\Module\Trade\Action\TransferGoods;

interface TransferGoodsRequestInterface
{
    public function getStorageId(): int;

    public function getAmount(): int;

    public function getDestinationTradePostId(): int;
}