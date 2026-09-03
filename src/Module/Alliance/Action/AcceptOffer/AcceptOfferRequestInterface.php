<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\AcceptOffer;

interface AcceptOfferRequestInterface
{
    public function getRelationId(): int;
}
