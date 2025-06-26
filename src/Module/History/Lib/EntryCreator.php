<?php

declare(strict_types=1);

namespace Stu\Module\History\Lib;

use Override;
use Stu\Component\History\HistoryTypeEnum;
use Stu\Lib\Map\EntityWithLocationInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\HistoryRepositoryInterface;

final class EntryCreator implements EntryCreatorInterface
{
    public function __construct(private HistoryRepositoryInterface $historyRepository) {}

    #[Override]
    public function addEntry(
        string $text,
        int $sourceUserId,
        Spacecraft|Colony|Alliance $target
    ): void {

        if ($target instanceof Spacecraft) {
            $type = $target->isStation() ? HistoryTypeEnum::STATION : HistoryTypeEnum::SHIP;
            $targetUser = $target->getUser();
        } elseif ($target instanceof Colony) {
            $type = HistoryTypeEnum::COLONY;
            $targetUser = $target->getUser();
        } else {
            $type = HistoryTypeEnum::ALLIANCE;
            $targetUser = $target->getFounder()->getUser();
        }

        $location = $target instanceof EntityWithLocationInterface
            ? $target->getLocation()
            : null;

        $this->createEntry(
            $type,
            $text,
            $sourceUserId,
            $targetUser->getId(),
            $location
        );
    }

    #[Override]
    public function createEntry(
        HistoryTypeEnum $type,
        string $text,
        int $sourceUserId,
        int $targetUserId,
        ?Location $location = null
    ): void {
        $entry = $this->historyRepository->prototype();
        $entry->setText($text);
        $entry->setSourceUserId($sourceUserId);
        $entry->setTargetUserId($targetUserId);
        $entry->setDate(time());
        $entry->setType($type);
        $entry->setLocation($location);

        $this->historyRepository->save($entry);
    }
}
