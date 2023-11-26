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
        $this->addEntry(HistoryTypeEnum::SHIP, $text, $userId);
    }

    public function addStationEntry(
        string $text,
        int $userId = UserEnum::USER_NOONE
    ): void {
        $this->addEntry(HistoryTypeEnum::STATION, $text, $userId);
    }

    public function addColonyEntry(
        string $text,
        int $userId = UserEnum::USER_NOONE
    ): void {
        $this->addEntry(HistoryTypeEnum::COLONY, $text, $userId);
    }

    public function addAllianceEntry(
        string $text,
        int $userId = UserEnum::USER_NOONE
    ): void {
        $this->addEntry(HistoryTypeEnum::ALLIANCE, $text, $userId);
    }

    public function addOtherEntry(
        string $text,
        int $userId = UserEnum::USER_NOONE
    ): void {
        $this->addEntry(HistoryTypeEnum::OTHER, $text, $userId);
    }

    private function addEntry(
        HistoryTypeEnum $type,
        string $text,
        int $userId
    ): void {
        $entry = $this->historyRepository->prototype();
        $entry->setText($text);
        $entry->setUserId($userId);
        $entry->setDate(time());
        $entry->setType($type);

        $this->historyRepository->save($entry);
    }
}
