<?php

declare(strict_types=1);

namespace Stu\Module\History\Lib;

use Stu\Component\Game\GameEnum;
use Stu\Orm\Repository\HistoryRepositoryInterface;

final class EntryCreator implements EntryCreatorInterface
{
    public const HISTORY_SHIP = 1;
    public const HISTORY_STATION = 2;
    public const HISTORY_COLONY = 3;
    public const HISTORY_ALLIANCE = 4;
    public const HISTORY_OTHER = 5;
    private HistoryRepositoryInterface $historyRepository;

    public function __construct(
        HistoryRepositoryInterface $historyRepository
    ) {
        $this->historyRepository = $historyRepository;
    }

    public function addShipEntry(
        string $text,
        int $userId = GameEnum::USER_NOONE
    ): void {
        $this->addEntry(self::HISTORY_SHIP, $text, $userId);
    }

    public function addStationEntry(
        string $text,
        int $userId = GameEnum::USER_NOONE
    ): void {
        $this->addEntry(self::HISTORY_STATION, $text, $userId);
    }

    public function addColonyEntry(
        string $text,
        int $userId = GameEnum::USER_NOONE
    ): void {
        $this->addEntry(self::HISTORY_COLONY, $text, $userId);
    }

    public function addAllianceEntry(
        string $text,
        int $userId = GameEnum::USER_NOONE
    ): void {
        $this->addEntry(self::HISTORY_ALLIANCE, $text, $userId);
    }

    private function addEntry(
        int $typeId,
        string $text,
        int $userId
    ): void {
        $entry = $this->historyRepository->prototype();
        $entry->setText($text);
        $entry->setUserId($userId);
        $entry->setDate(time());
        $entry->setType($typeId);

        $this->historyRepository->save($entry);
    }
}
