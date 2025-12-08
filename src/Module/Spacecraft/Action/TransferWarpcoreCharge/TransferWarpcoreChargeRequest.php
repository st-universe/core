<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\TransferWarpcoreCharge;

use request;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class TransferWarpcoreChargeRequest implements TransferWarpcoreChargeRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getSpacecraftId(): int
    {
        return $this->queryParameter('id')->int()->required();
    }

    #[\Override]
    public function getTargetSpacecraftIds(): array
    {
        return array_map('intval', request::postArray('spacecrafts'));
    }

    #[\Override]
    public function getTransferAmounts(): array
    {
        return array_map('intval', request::postArray('warpcore_transfer'));
    }
}
