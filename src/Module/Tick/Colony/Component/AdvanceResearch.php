<?php

namespace Stu\Module\Tick\Colony\Component;

use Stu\Module\Research\ResearchStateFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class AdvanceResearch implements ColonyTickComponentInterface
{
    private ResearchedRepositoryInterface $researchedRepository;

    private ResearchStateFactoryInterface $researchStateFactory;

    /** @var array<int, int> */
    private array $userToResearchCommodity = [];

    public function __construct(
        ResearchedRepositoryInterface $researchedRepository,
        ResearchStateFactoryInterface $researchStateFactory
    ) {
        $this->researchedRepository = $researchedRepository;
        $this->researchStateFactory = $researchStateFactory;
    }


    public function work(ColonyInterface $colony, array &$production): void
    {
        $researches = $this->researchedRepository->getCurrentResearch($colony->getUser());
        $currentResearch = $researches === [] ? null : current($researches);
        $waitingResearch = count($researches) > 1 ? $researches[1] : null;

        if ($currentResearch === null) {
            return;
        }

        $commodityId = $currentResearch->getResearch()->getCommodityId();
        if (!array_key_exists($commodityId, $production)) {
            return;
        }

        if (!$this->isCommodityAllowed($commodityId, $colony->getUser()->getId())) {
            return;
        }

        $remaining = $this->advanceResearchState($currentResearch, $production[$commodityId]->getProduction());

        if ($remaining < 1) {
            return;
        }
        if ($waitingResearch === null) {
            return;
        }

        $commodityId = $waitingResearch->getResearch()->getCommodityId();
        if (!$this->isCommodityAllowed($commodityId, $colony->getUser()->getId())) {
            return;
        }

        $this->advanceResearchState($waitingResearch, $remaining);
    }

    private function isCommodityAllowed(int $commodityId, int $userId): bool
    {
        if (!array_key_exists($userId, $this->userToResearchCommodity)) {
            $this->userToResearchCommodity[$userId] = $commodityId;

            return true;
        }

        return $commodityId === $this->userToResearchCommodity[$userId];
    }

    private function advanceResearchState(ResearchedInterface $researched, int $amount): int
    {
        return $this->researchStateFactory->createResearchState()->advance(
            $researched,
            $amount
        );
    }
}
