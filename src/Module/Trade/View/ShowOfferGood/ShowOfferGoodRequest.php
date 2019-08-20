<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOfferGood;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowOfferGoodRequest implements ShowOfferGoodRequestInterface
{
    use CustomControllerHelperTrait;

    public function getTradePostId(): int
    {
        return $this->queryParameter('postid')->int()->required();
    }

    public function getGoodId(): int
    {
        return $this->queryParameter('goodid')->int()->required();
    }
}