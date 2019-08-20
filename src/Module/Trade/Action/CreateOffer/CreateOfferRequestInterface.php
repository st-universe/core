<?php

namespace Stu\Module\Trade\Action\CreateOffer;

interface CreateOfferRequestInterface
{
    public function getStorageId(): int;

    public function getWantedGoodId(): int;

    public function getWantedAmount(): int;

    public function getGiveGoodId(): int;

    public function getGiveAmount(): int;

    public function getOfferAmount(): int;
}