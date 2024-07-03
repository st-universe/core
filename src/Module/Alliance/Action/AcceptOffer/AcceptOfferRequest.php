<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AcceptOffer;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class AcceptOfferRequest implements AcceptOfferRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getRelationId(): int
    {
        return $this->queryParameter('al')->int()->required();
    }
}
