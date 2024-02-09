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
        int $sourceUserId = UserEnum::USER_NOONE,
        int $targetUserId = UserEnum::USER_NOONE
    ): void {
        $this->addEntry(HistoryTypeEnum::SHIP, $text, $sourceUserId, $targetUserId);
    }

    public function addStationEntry(
        string $text,
        int $sourceUserId = UserEnum::USER_NOONE,
        int $targetUserId = UserEnum::USER_NOONE
    ): void {
        $this->addEntry(HistoryTypeEnum::STATION, $text, $sourceUserId, $targetUserId);
    }

    public function addColonyEntry(
        string $text,
        int $sourceUserId = UserEnum::USER_NOONE,
        int $targetUserId = UserEnum::USER_NOONE
    ): void {
        $this->addEntry(HistoryTypeEnum::COLONY, $text, $sourceUserId, $targetUserId);
    }

    public function addAllianceEntry(
        string $text,
        int $sourceUserId = UserEnum::USER_NOONE,
        int $targetUserId = UserEnum::USER_NOONE
    ): void {
        $this->addEntry(HistoryTypeEnum::ALLIANCE, $text, $sourceUserId, $targetUserId);
    }

    public function addOtherEntry(
        string $text,
        int $sourceUserId = UserEnum::USER_NOONE,
        int $targetUserId = UserEnum::USER_NOONE
    ): void {
        $this->addEntry(HistoryTypeEnum::OTHER, $text, $sourceUserId, $targetUserId);
    }

    private function addEntry(
        HistoryTypeEnum $type,
        string $text,
        int $sourceUserId,
        int $targetUserId
    ): void {
        $entry = $this->historyRepository->prototype();
        $entry->setText($text);
        $entry->setSourceUserId($sourceUserId);
        $entry->setTargetUserId($targetUserId);
        $entry->setDate(time());
        $entry->setType($type);

        $this->historyRepository->save($entry);
    }
}