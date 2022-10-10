<?php

namespace Stu\Module\Trade\View\ShowOfferCommodity;

interface ShowOfferCommodityRequestInterface
{
    public function getTradePostId(): int;

    public function getCommodityId(): int;
}
