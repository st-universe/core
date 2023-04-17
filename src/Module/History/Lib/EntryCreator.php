<?php

declare(strict_types=1);

namespace Stu\Module\History\Lib;

use Stu\Component\History\HistoryTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\HistoryRepositoryInterface;

final class EntryCreator implements EntryCreatorInterface
{
    private HistoryRepositoryInterface $historyRepository;

    public function __construct(
        HistoryRepositoryInterface $historyRepository
    ) {
        $this->historyRepository = $historyRepository;
    }

    public function addShipEntry(
        string $text,
        int $userId = UserEnum::USER_NOONE
    ): void {
        $this->addEntry(HistoryTypeEnum::HISTORY_TYPE_SHIP, $text, $userId);
    }

    public function addStationEntry(
        string $text,
        int $userId = UserEnum::USER_NOONE
    ): void {
        $this->addEntry(HistoryTypeEnum::HISTORY_TYPE_STATION, $text, $userId);
    }

    public function addColonyEntry(
        string $text,
        int $userId = UserEnum::USER_NOONE
    ): void {
        $this->addEntry(HistoryTypeEnum::HISTORY_TYPE_COLONY, $text, $userId);
    }

    public function addAllianceEntry(
        string $text,
        int $userId = UserEnum::USER_NOONE
    ): void {
        $this->addEntry(HistoryTypeEnum::HISTORY_TYPE_ALLIANCE, $text, $userId);
    }

    public function addOtherEntry(
        string $text,
        int $userId = UserEnum::USER_NOONE
    ): void {
        $this->addEntry(HistoryTypeEnum::HISTORY_TYPE_OTHER, $text, $userId);
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
