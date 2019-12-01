<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\TradeOfferInterface;
use Stu\Orm\Entity\UserInterface;

final class TradeOfferItem implements TradeOfferItemInterface
{
    private TradeOfferInterface $tradeOffer;

    private UserInterface $currentUser;

    public function __construct(
        TradeOfferInterface $tradeOffer,
        UserInterface $currentUser
    ) {
        $this->tradeOffer = $tradeOffer;
        $this->currentUser = $currentUser;
    }

    public function getOffer(): TradeOfferInterface
    {
        return $this->tradeOffer;
    }

    public function isOwnOffer(): bool
    {
        return $this->tradeOffer->getUser() === $this->currentUser;
    }
}
