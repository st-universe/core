<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Psr\EventDispatcher\EventDispatcherInterface;
use Stu\Component\Crew\Skill\Event\CrewExperienceEvent;
use Stu\Component\Crew\Skill\SkillEnhancementEnum;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Module\Message\View\ShowWriteQuickPm\ShowWriteQuickPm;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;

final class QuickPmCrewExperience implements QuickPmCrewExperienceInterface
{
    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private StationRepositoryInterface $stationRepository,
        private ColonyRepositoryInterface $colonyRepository,
        private PlayerRelationDeterminatorInterface $playerRelationDeterminator,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    #[\Override]
    public function awardExperience(
        User $sender,
        int $recipientId,
        int $sourceId,
        int $sourceType,
        int $targetId,
        int $targetType
    ): void {
        $source = $this->getSourceSpacecraft($sourceId, $sourceType);
        if ($source === null || $source->getUser()->getId() !== $sender->getId()) {
            return;
        }

        $target = $this->getTarget($targetId, $targetType);
        if (
            $target === null
            || $target->getUser()->getId() !== $recipientId
            || $target->getUser()->isNpc()
            || $source->getLocation()->getId() !== $target->getLocation()->getId()
            || $this->playerRelationDeterminator->isFriend($sender, $target->getUser())
        ) {
            return;
        }

        $this->eventDispatcher->dispatch(new CrewExperienceEvent(
            $source,
            SkillEnhancementEnum::FOREIGN_CONTACT_MESSAGE
        ));
    }

    private function getSourceSpacecraft(int $sourceId, int $sourceType): ?Spacecraft
    {
        return match ($sourceType) {
            ShowWriteQuickPm::TYPE_SHIP => $this->shipRepository->find($sourceId),
            ShowWriteQuickPm::TYPE_STATION => $this->stationRepository->find($sourceId),
            default => null
        };
    }

    private function getTarget(int $targetId, int $targetType): Spacecraft|Colony|null
    {
        return match ($targetType) {
            ShowWriteQuickPm::TYPE_SHIP => $this->shipRepository->find($targetId),
            ShowWriteQuickPm::TYPE_STATION => $this->stationRepository->find($targetId),
            ShowWriteQuickPm::TYPE_COLONY => $this->colonyRepository->find($targetId),
            default => null
        };
    }
}
