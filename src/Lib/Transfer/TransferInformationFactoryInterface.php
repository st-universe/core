<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Stu\Orm\Entity\UserInterface;

interface TransferInformationFactoryInterface
{
    public function createTransferInformation(
        int $sourceId,
        TransferEntityTypeEnum $sourceType,
        int $targetId,
        TransferEntityTypeEnum $targetType,
        TransferTypeEnum $currentType,
        bool $isUnload,
        UserInterface $user,
        bool $checkForEntityLock
    ): TransferInformation;
}
