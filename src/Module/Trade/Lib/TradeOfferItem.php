<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Override;
use Stu\Orm\Entity\TradeOfferInterface;
use Stu\Orm\Entity\UserInterface;

final class TradeOfferItem implements TradeOfferItemInterface
{
    public function __construct(private TradeOfferInterface $tradeOffer, private UserInterface $currentUser)
    {
    }

    #[Override]
    public function getOffer(): TradeOfferInterface
    {
        return $this->tradeOffer;
    }

    #[Override]
    public function isOwnOffer(): bool
    {
        return $this->tradeOffer->getUser() === $this->currentUser;
    }
}
