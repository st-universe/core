<?php

namespace Stu\Module\Research;

use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\ResearchInterface;

interface TalSelectedTechInterface
{
    public function getResearch(): ResearchInterface;

    public function getResearchState(): ?ResearchedInterface;

    /** @return array<string, TechDependency> */
    public function getDistinctExcludeNames(): array;

    public function hasExcludes(): bool;

    /** @return array<string, TechDependency> */
    public function getDistinctPositiveDependencyNames(): array;

    public function hasPositiveDependencies(): bool;

    public function getDonePoints(): int;

    public function isResearchFinished(): bool;

    public function getStatusBar(): string;

    public function getWikiLink(): string;

    /** @return array<BuildingInterface> */
    public function getBuildings(): array;
}
