<?php

namespace Stu\Module\Trade\Action\DealsTakeOffer;

interface DealsTakeOfferRequestInterface
{
    public function getDealId(): int;

    public function getAmount(): int;
}