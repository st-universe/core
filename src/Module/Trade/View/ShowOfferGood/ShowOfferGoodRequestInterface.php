<?php

namespace Stu\Module\Trade\View\ShowOfferGood;

interface ShowOfferGoodRequestInterface
{
    public function getTradePostId(): int;

    public function getGoodId(): int;
}