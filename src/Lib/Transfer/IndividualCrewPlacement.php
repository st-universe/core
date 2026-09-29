<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

final class IndividualCrewPlacement
{
    private function __construct(
        public readonly int $id,
        public readonly int $side,
        public readonly int $slot,
        public readonly mixed $originalSide,
        public readonly mixed $originalSlot
    ) {}

    public static function fromInput(mixed $input): ?self
    {
        if (!is_array($input)
            || !is_int($input['id'] ?? null)
            || !is_int($input['side'] ?? null)
            || !is_int($input['slot'] ?? null)) {
            return null;
        }

        return new self(
            $input['id'],
            $input['side'],
            $input['slot'],
            $input['originalSide'] ?? null,
            $input['originalSlot'] ?? null
        );
    }
}
