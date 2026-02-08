<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Stu\Component\Research\ResearchModeEnum;
use Stu\Orm\Entity\Research;
use Stu\Orm\Entity\ResearchDependency;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\ResearchDependencyRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;

final class TechlistRetriever implements TechlistRetrieverInterface
{
    public function __construct(private ResearchRepositoryInterface $researchRepository, private ResearchDependencyRepositoryInterface $researchDependencyRepository, private ResearchedRepositoryInterface $researchedRepository, private FactionRepositoryInterface $factionRepository) {}

    #[\Override]
    public function getResearchList(User $user): array
    {
        $researchedList = $this->getResearchedList($user);

        $researchedIdsWithUnfinished = array_map(
            fn (Researched $researched): int => $researched->getResearch()->getId(),
            $researchedList
        );

        $researchedIdsOnlyFinished = array_map(
            fn (Researched $researched): int => $researched->getResearch()->getId(),
            array_filter(
                $researchedList,
                fn (Researched $researched): bool => $researched->getFinished() > 0
            )
        );

        $result = $this->researchRepository->getAvailableResearch($user->getId());
        $list_result = [];

        //load dependencies
        $allDependencies = $this->loadDependencies();

        //load excludes
        $excludes = $this->loadExcludes();

        // calculate possible research items
        foreach ($result as $research) {
            // check for existent user award
            if ($research->getNeededAwardId() !== null && !$user->hasAward($research->getNeededAwardId())) {
                continue;
            }

            $researchId = $research->getId();

            // excludelogic
            if (isset($excludes[$researchId])) {
                foreach ($excludes[$researchId] as $exclude) {
                    if (in_array($exclude->getResearchId(), $researchedIdsWithUnfinished)) {
                        continue 2;
                    }
                }
            }

            // dependency logic
            if (isset($allDependencies[$researchId])) {

                $dependencies = $allDependencies[$researchId];

                // check for AND condition
                foreach ($dependencies['AND'] as $and_condition) {
                    if (!in_array($and_condition, $researchedIdsOnlyFinished)) {
                        continue 2;
                    }
                }

                // check for OR condition
                if (!empty($dependencies['OR'])) {
                    $or_condition_met = false;
                    foreach ($dependencies['OR'] as $or_condition) {
                        if (in_array($or_condition, $researchedIdsOnlyFinished)) {
                            $or_condition_met = true;
                            break;
                        }
                    }
                    if (!$or_condition_met) {
                        continue;
                    }
                }
            }

            $list_result[$researchId] = $research;
        }


        foreach ($this->factionRepository->findAll() as $faction) {
            $startResearch = $faction->getStartResearch();
            if ($startResearch !== null) {
                $startResearchId = $startResearch->getId();
                if (isset($list_result[$startResearchId])) {
                    unset($list_result[$startResearchId]);
                }
            }
        }

        return $list_result;
    }

    #[\Override]
    public function canResearch(User $user, int $researchId): ?Research
    {
        return $this->getResearchList($user)[$researchId] ?? null;
    }

    /** @return array<int, array<string, array<int>>> */
    private function loadDependencies(): array
    {
        $allDependencies = [];

        $allDependencies_result = $this->researchDependencyRepository->getByMode(
            [ResearchModeEnum::REQUIRE->value, ResearchModeEnum::REQUIRE_SOME->value]
        );

        foreach ($allDependencies_result as $dependency) {
            $research_id = $dependency->getResearchId();
            $mode = $dependency->getMode();

            if (!isset($allDependencies[$research_id])) {
                $allDependencies[$research_id] = [
                    'AND' => [],
                    'OR' => []
                ];
            }

            if ($mode === ResearchModeEnum::REQUIRE) {
                $allDependencies[$research_id]['AND'][] = $dependency->getDependsOn();
            } elseif ($mode === ResearchModeEnum::REQUIRE_SOME) {
                $allDependencies[$research_id]['OR'][] = $dependency->getDependsOn();
            }
        }

        return $allDependencies;
    }

    /** @return array<int, array<ResearchDependency>> */
    private function loadExcludes(): array
    {
        $excludes = [];
        $exclude_result = $this->researchDependencyRepository->getByMode([ResearchModeEnum::EXCLUDE->value]);

        foreach ($exclude_result as $dependency) {
            $research_id = $dependency->getDependsOn();
            if (array_key_exists($research_id, $excludes) === false) {
                $excludes[$research_id] = [];
            }
            $excludes[$research_id][] = $dependency;
        }

        return $excludes;
    }

    #[\Override]
    public function getResearchedList(User $user): array
    {
        return $this->researchedRepository->getListByUser($user->getId());
    }
}
