<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

interface TransferInformationFactoryInterface
{
    public function createTransferInformation(
        int $sourceId,
        TransferEntityTypeEnum $sourceType,
        int $targetId,
        TransferEntityTypeEnum $targetType,
        TransferTypeEnum $currentType,
        bool $isUnload
    ): TransferInformation;
}
