<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOfferCommodity;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowOfferCommodityRequest implements ShowOfferCommodityRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getTradePostId(): int
    {
        return $this->queryParameter('postid')->int()->required();
    }

    #[Override]
    public function getCommodityId(): int
    {
        return $this->queryParameter('commodityid')->int()->required();
    }
}
