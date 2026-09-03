<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\TradeOffer;

interface TradeOfferItemInterface
{
    public function getOffer(): TradeOffer;

    public function isOwnOffer(): bool;
}
