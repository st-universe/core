<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOfferCommodity;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowOfferCommodityRequest implements ShowOfferCommodityRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getTradePostId(): int
    {
        return $this->parameter('postid')->int()->required();
    }

    #[\Override]
    public function getCommodityId(): int
    {
        return $this->parameter('commodityid')->int()->required();
    }
}
