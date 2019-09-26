<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Stu\Component\Research\ResearchEnum;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Repository\ResearchDependencyRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;

final class TechlistRetriever implements TechlistRetrieverInterface
{
    private $researchRepository;

    private $researchDependencyRepository;

    private $researchedRepository;

    public function __construct(
        ResearchRepositoryInterface $researchRepository,
        ResearchDependencyRepositoryInterface $researchDependencyRepository,
        ResearchedRepositoryInterface $researchedRepository
    ) {
        $this->researchRepository = $researchRepository;
        $this->researchDependencyRepository = $researchDependencyRepository;
        $this->researchedRepository = $researchedRepository;
    }

    public function getResearchList(int $userId): array
    {
        $finished_list = array_map(
            function (ResearchedInterface $researched): int {
                return $researched->getResearch()->getId();
            },
            array_filter(
                $this->getFinishedResearchList($userId),
                function (ResearchedInterface $researched): bool {
                    return $researched->getFinished() > 0;
                }
            )
        );

        $result = $this->researchRepository->getAvailableResearch($userId);
        $list_result = [];

        $dependencies = [];
        $dependencies_result = $this->researchDependencyRepository->getByMode(
            [ResearchEnum::RESEARCH_MODE_REQUIRE, ResearchEnum::RESEARCH_MODE_REQUIRE_SOME]
        );
        $excludes = [];
        $exclude_result = $this->researchDependencyRepository->getByMode([ResearchEnum::RESEARCH_MODE_EXCLUDE]);

        foreach ($dependencies_result as $dependency) {
            $research_id = $dependency->getResearchId();
            if (array_key_exists($research_id, $dependencies) === false) {
                $dependencies[$research_id] = [];
            }
            $dependencies[$research_id][] = $dependency;
        }
        foreach ($exclude_result as $dependency) {
            $research_id = $dependency->getDependsOn();
            if (array_key_exists($research_id, $dependencies) === false) {
                $excludes[$research_id] = [];
            }
            $excludes[$research_id][] = $dependency;
        }

        foreach ($result as $obj) {
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

        // @todo check if needed. default tech should already be known
        foreach (getDefaultTechs() as $research_id) {
            if (isset($list_result[$research_id])) {
                unset($list_result[$research_id]);
            }
        }

        return $list_result;
    }

    public function getFinishedResearchList(int $userId): array
    {
        $result = $this->researchedRepository->getListByUser($userId);
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
