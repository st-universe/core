<?php

declare(strict_types=1);

namespace Stu\Module\History\Lib;

use Override;
use Stu\Component\History\HistoryTypeEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\HistoryRepositoryInterface;

final class EntryCreator implements EntryCreatorInterface
{
    public function __construct(private HistoryRepositoryInterface $historyRepository) {}

    #[Override]
    public function addEntry(
        string $text,
        int $sourceUserId,
        SpacecraftInterface|PlanetFieldHostInterface|AllianceInterface $target
    ): void {

        if ($target instanceof SpacecraftInterface) {
            $type = $target->isStation() ? HistoryTypeEnum::STATION : HistoryTypeEnum::SHIP;
            $targetUser = $target->getUser();
        } elseif ($target instanceof PlanetFieldHostInterface) {
            $type = HistoryTypeEnum::COLONY;
            $targetUser = $target->getUser();
        } else {
            $type = HistoryTypeEnum::ALLIANCE;
            $targetUser = $target->getFounder()->getUser();
        }

        $this->createEntry($type, $text, $sourceUserId, $targetUser->getId());
    }

    private function createEntry(
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
