<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

final class IndividualCrewTransferSelection
{
    public function __construct(
        public readonly int $sourceCount,
        public readonly int $targetCount,
        public readonly int $arrivalsAtSource,
        public readonly int $arrivalsAtTarget,
        public readonly int $originalTargetCount,
        public readonly bool $ownsTarget,
        public readonly bool $hasChanges
    ) {}
}
