<?php

namespace Stu\Module\Trade\Action\CreateOffer;

interface CreateOfferRequestInterface
{
    public function getStorageId(): int;

    public function getWantedCommodityId(): int;

    public function getWantedAmount(): int;

    public function getGiveCommodityId(): int;

    public function getGiveAmount(): int;

    public function getOfferAmount(): int;
}
