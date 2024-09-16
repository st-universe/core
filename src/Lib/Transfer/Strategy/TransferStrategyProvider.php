<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Strategy;

use RuntimeException;
use Stu\Lib\Transfer\TransferTypeEnum;

class TransferStrategyProvider implements TransferStrategyProviderInterface
{
    /** @param array<TransferStrategyInterface> $transferStrategies */
    public function __construct(
        private array $transferStrategies
    ) {}

    public function getTransferStrategy(TransferTypeEnum $type): TransferStrategyInterface
    {
        if (!array_key_exists($type->value, $this->transferStrategies)) {
            throw new RuntimeException(sprintf('transfer strategy with typeValue %d does not exist', $type->value));
        }

        return $this->transferStrategies[$type->value];
    }
}
