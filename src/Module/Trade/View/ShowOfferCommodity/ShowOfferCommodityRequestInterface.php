<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOfferCommodity;

interface ShowOfferCommodityRequestInterface
{
    public function getTradePostId(): int;

    public function getCommodityId(): int;
}
