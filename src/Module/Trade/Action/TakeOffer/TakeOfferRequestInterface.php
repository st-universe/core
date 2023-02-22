<?php

namespace Stu\Module\Trade\Action\TakeOffer;

interface TakeOfferRequestInterface
{
    public function getOfferId(): int;

    public function getAmount(): int;
}
