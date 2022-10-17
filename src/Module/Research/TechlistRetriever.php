<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Stu\Component\Player\UserAwardEnum;
use Stu\Component\Research\ResearchEnum;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\ResearchDependencyRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;

final class TechlistRetriever implements TechlistRetrieverInterface
{
    private ResearchRepositoryInterface $researchRepository;

    private ResearchDependencyRepositoryInterface $researchDependencyRepository;

    private ResearchedRepositoryInterface $researchedRepository;

    private FactionRepositoryInterface $factionRepository;

    public function __construct(
        ResearchRepositoryInterface $researchRepository,
        ResearchDependencyRepositoryInterface $researchDependencyRepository,
        ResearchedRepositoryInterface $researchedRepository,
        FactionRepositoryInterface $factionRepository
    ) {
        $this->researchRepository = $researchRepository;
        $this->researchDependencyRepository = $researchDependencyRepository;
        $this->researchedRepository = $researchedRepository;
        $this->factionRepository = $factionRepository;
    }

    public function getResearchList(UserInterface $user): array
    {
        $finished_list = array_map(
            function (ResearchedInterface $researched): int {
                return $researched->getResearch()->getId();
            },
            array_filter(
                $this->getFinishedResearchList($user),
                function (ResearchedInterface $researched): bool {
                    return $researched->getFinished() > 0;
                }
            )
        );

        $result = $this->researchRepository->getAvailableResearch($user->getId());
        $list_result = [];

        //load dependencies
        $dependencies = $this->loadDependencies();

        //load excludes
        $excludes = $this->loadExcludes();

        // calculate possible research items
        foreach ($result as $obj) {

            // check for existent user award
            if ($obj->getNeededAwardId() !== null) {
                if (!$user->hasAward($obj->getNeededAwardId())) {
                    continue;
                }
            }

            $key = $obj->getId();
            if (isset($excludes[$key])) {
                foreach ($excludes[$key] as $exclude) {
                    if (
                        in_array($exclude->getResearchId(), $finished_list)
                    ) {
                        continue 2;
                    }
                }
            }
            if (!isset($dependencies[$key])) {
                $list_result[$key] = $obj;
                continue;
            }
            $grouped_list = array();
            foreach ($dependencies[$key] as $dependency) {
                if (!isset($grouped_list[$dependency->getMode()])) {
                    $grouped_list[$dependency->getMode()] = array();
                }
                if ($dependency->getMode() != ResearchEnum::RESEARCH_MODE_EXCLUDE) {
                    $grouped_list[$dependency->getMode()][] = $dependency;
                }
            }
            if (count($grouped_list) > 0) {
                foreach ($grouped_list as $group) {
                    $found = false;
                    foreach ($group as $dependency) {
                        if (in_array($dependency->getDependsOn(), $finished_list)) {
                            $found = true;
                        }
                    }
                    if (!$found) {
                        continue 2;
                    }
                }
            }

            $list_result[$key] = $obj;
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

    private function loadDependencies(): array
    {
        $dependencies = [];

        $dependencies_result = $this->researchDependencyRepository->getByMode(
            [ResearchEnum::RESEARCH_MODE_REQUIRE, ResearchEnum::RESEARCH_MODE_REQUIRE_SOME]
        );

        foreach ($dependencies_result as $dependency) {
            $research_id = $dependency->getResearchId();
            if (array_key_exists($research_id, $dependencies) === false) {
                $dependencies[$research_id] = [];
            }
            $dependencies[$research_id][] = $dependency;
        }

        return $dependencies;
    }

    private function loadExcludes(): array
    {
        $excludes = [];
        $exclude_result = $this->researchDependencyRepository->getByMode([ResearchEnum::RESEARCH_MODE_EXCLUDE]);

        foreach ($exclude_result as $dependency) {
            $research_id = $dependency->getDependsOn();
            if (array_key_exists($research_id, $excludes) === false) {
                $excludes[$research_id] = [];
            }
            $excludes[$research_id][] = $dependency;
        }

        return $excludes;
    }

    public function getFinishedResearchList(UserInterface $user): array
    {
        $result = $this->researchedRepository->getListByUser($user->getId());
        usort(
            $result,
            function (ResearchedInterface $a, ResearchedInterface $b): int {
                if ($a->getActive() != $b->getActive()) {
                    return $b->getActive() <=> $a->getActive();
                }
                return $b->getFinished() <=> $a->getFinished();
            }
        );

        return $result;
    }
}
