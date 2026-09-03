<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowTakeOffer;

interface ShowTakeOfferRequestInterface
{
    public function getOfferId(): int;
}
