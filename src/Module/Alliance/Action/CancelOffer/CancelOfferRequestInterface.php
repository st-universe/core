<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CancelOffer;

interface CancelOfferRequestInterface
{
    public function getRelationId(): int;
}
