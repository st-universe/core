<?php

namespace Stu\Module\Tick\Colony\Component;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Research\ResearchStateFactoryInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class AdvanceResearch implements ColonyTickComponentInterface
{
    /** @var array<int, int> */
    private array $userToResearchCommodity = [];

    public function __construct(
        private readonly ResearchedRepositoryInterface $researchedRepository,
        private readonly ResearchStateFactoryInterface $researchStateFactory
    ) {}


    #[Override]
    public function work(Colony $colony, array &$production, InformationInterface $information): void
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

    private function advanceResearchState(Researched $researched, int $amount): int
    {
        return $this->researchStateFactory->createResearchState()->advance(
            $researched,
            $amount
        );
    }
}
