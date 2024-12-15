<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use RuntimeException;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

enum TransferEntityTypeEnum: string
{
    case SHIP = 'ship';
    case STATION = 'station';
    case COLONY = 'colony';
    case TRUMFIELD = 'trumfield';

    public function getName(): string
    {
        return match ($this) {
            self::SHIP => 'Schiff',
            self::STATION => 'Station',
            self::COLONY => 'Kolonie',
            self::TRUMFIELD => 'Trümmerfeld',
        };
    }

    public function getViewIdentifier(): string
    {
        return match ($this) {
            self::SHIP,
            self::STATION => ShowSpacecraft::VIEW_IDENTIFIER,
            self::COLONY => ShowColony::VIEW_IDENTIFIER,
            default => throw new RuntimeException(sprintf('unsupported entity type: %s', $this->value))
        };
    }

    /** @return array<TransferTypeEnum> */
    public function getPossibleTransferTypes(): array
    {
        return match ($this) {
            self::SHIP,
            self::STATION,
            self::COLONY => TransferTypeEnum::cases(),
            self::TRUMFIELD => [TransferTypeEnum::COMMODITIES]
        };
    }

    //TODO use this to check source and target type
    /** @return array<TransferEntityTypeEnum> */
    public function getAllowedTransferSources(): array
    {
        return match ($this) {
            self::SHIP,
            self::STATION,
            self::TRUMFIELD => [self::SHIP, self::STATION, self::COLONY],
            self::COLONY => [self::SHIP, self::STATION]
        };
    }
}
