<?php

namespace Stu\Module\Trade\Action\CancelOffer;

interface CancelOfferRequestInterface
{
    public function getOfferId(): int;

    public function getView(): ?string;
}
