<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Strategy;

use Stu\Lib\Transfer\TransferTypeEnum;

interface TransferStrategyProviderInterface
{
    public function getTransferStrategy(TransferTypeEnum $type): TransferStrategyInterface;
}
