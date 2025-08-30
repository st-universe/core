<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Override;
use Stu\Orm\Entity\TradeOffer;
use Stu\Orm\Entity\User;

final class TradeOfferItem implements TradeOfferItemInterface
{
    public function __construct(private TradeOffer $tradeOffer, private User $currentUser) {}

    #[Override]
    public function getOffer(): TradeOffer
    {
        return $this->tradeOffer;
    }

    #[Override]
    public function isOwnOffer(): bool
    {
        return $this->tradeOffer->getUser()->getId() === $this->currentUser->getId();
    }
}
