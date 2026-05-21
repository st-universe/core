<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Repair;

final class CancelRepairResult
{
    public function __construct(
        private readonly bool $cancelled,
        private readonly int $refundedSpareParts = 0,
        private readonly int $refundedSystemComponents = 0
    ) {}

    public function isCancelled(): bool
    {
        return $this->cancelled;
    }

    public function getRefundedSpareParts(): int
    {
        return $this->refundedSpareParts;
    }

    public function getRefundedSystemComponents(): int
    {
        return $this->refundedSystemComponents;
    }

    public function hasRefundedCommodities(): bool
    {
        return $this->refundedSpareParts > 0 || $this->refundedSystemComponents > 0;
    }
}
