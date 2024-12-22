<?php

declare(strict_types=1);

namespace Stu\Html\Game;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Lib\Transfer\TransferTypeEnum;
use Stu\Module\Game\View\ShowTransfer\ShowTransfer;
use Stu\TwigTestCase;

class ShowTransferTest extends TwigTestCase
{
    #[Override]
    protected function getViewControllerClass(): string
    {
        return ShowTransfer::class;
    }

    public static function getCombinationsDataProvider(): array
    {
        //TODO more cases
        return [
            // SHIP -> STATION
            [42, 43, 0, TransferEntityTypeEnum::SHIP, TransferEntityTypeEnum::STATION, TransferTypeEnum::COMMODITIES],
            [42, 43, 0, TransferEntityTypeEnum::SHIP, TransferEntityTypeEnum::STATION, TransferTypeEnum::CREW],
            [42, 43, 1, TransferEntityTypeEnum::SHIP, TransferEntityTypeEnum::STATION, TransferTypeEnum::COMMODITIES],
            [42, 43, 1, TransferEntityTypeEnum::SHIP, TransferEntityTypeEnum::STATION, TransferTypeEnum::CREW],

            // STATION -> SHIP
            [43, 42, 0, TransferEntityTypeEnum::STATION, TransferEntityTypeEnum::SHIP, TransferTypeEnum::COMMODITIES],
            [43, 42, 0, TransferEntityTypeEnum::STATION, TransferEntityTypeEnum::SHIP, TransferTypeEnum::CREW],
            [43, 42, 1, TransferEntityTypeEnum::STATION, TransferEntityTypeEnum::SHIP, TransferTypeEnum::COMMODITIES],
            [43, 42, 1, TransferEntityTypeEnum::STATION, TransferEntityTypeEnum::SHIP, TransferTypeEnum::CREW],

            // STATION -> TRUMFIELD
            [43, 1, 0, TransferEntityTypeEnum::STATION, TransferEntityTypeEnum::TRUMFIELD, TransferTypeEnum::COMMODITIES],

            // SHIP -> COLONY
            [77, 42, 0, TransferEntityTypeEnum::SHIP, TransferEntityTypeEnum::COLONY, TransferTypeEnum::COMMODITIES],
            [77, 42, 1, TransferEntityTypeEnum::SHIP, TransferEntityTypeEnum::COLONY, TransferTypeEnum::COMMODITIES],

            // COLONY -> SHIP
            [42, 77, 0, TransferEntityTypeEnum::COLONY, TransferEntityTypeEnum::SHIP, TransferTypeEnum::COMMODITIES],
            [42, 77, 1, TransferEntityTypeEnum::COLONY, TransferEntityTypeEnum::SHIP, TransferTypeEnum::COMMODITIES],
        ];
    }

    #[DataProvider('getCombinationsDataProvider')]
    public function testHandle(
        int $id,
        int $target,
        int $isUnload,
        TransferEntityTypeEnum $sourceType,
        TransferEntityTypeEnum $targetType,
        TransferTypeEnum $transferType
    ): void {
        $this->renderSnapshot(101, [
            'id' => $id,
            'source_type' => $sourceType->value,
            'target' => $target,
            'target_type' => $targetType->value,
            'transfer_type' => $transferType->value,
            'is_unload' => $isUnload
        ]);
    }
}
