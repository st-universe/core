<?php

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\TradeOfferInterface;

interface TradeOfferItemInterface
{
    public function getOffer(): TradeOfferInterface;

    public function isOwnOffer(): bool;
}
