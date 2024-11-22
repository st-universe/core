<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\CancelOffer;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class CancelOfferRequest implements CancelOfferRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getOfferId(): int
    {
        return $this->parameter('offerid')->int()->required();
    }

    #[Override]
    public function getView(): ?string
    {
        return $this->parameter('view')->string()->defaultsTo(null);
    }
}
