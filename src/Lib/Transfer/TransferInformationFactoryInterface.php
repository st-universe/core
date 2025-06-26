<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Stu\Orm\Entity\User;

interface TransferInformationFactoryInterface
{
    public function createTransferInformation(
        int $sourceId,
        TransferEntityTypeEnum $sourceType,
        int $targetId,
        TransferEntityTypeEnum $targetType,
        TransferTypeEnum $currentType,
        bool $isUnload,
        User $user,
        bool $checkForEntityLock
    ): TransferInformation;
}
