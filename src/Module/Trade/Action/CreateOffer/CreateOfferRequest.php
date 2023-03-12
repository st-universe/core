<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\CreateOffer;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreateOfferRequest implements CreateOfferRequestInterface
{
    use CustomControllerHelperTrait;

    public function getStorageId(): int
    {
        return $this->bodyParameter('storid')->int()->required();
    }

    public function getWantedCommodityId(): int
    {
        return $this->bodyParameter('wgid')->int()->required();
    }

    public function getWantedAmount(): int
    {
        return $this->bodyParameter('wcount')->int()->defaultsTo(0);
    }

    public function getGiveCommodityId(): int
    {
        return $this->bodyParameter('ggid')->int()->required();
    }

    public function getGiveAmount(): int
    {
        return $this->bodyParameter('gcount')->int()->defaultsTo(0);
    }

    public function getOfferAmount(): int
    {
        return $this->bodyParameter('amount')->int()->defaultsTo(0);
    }
}
