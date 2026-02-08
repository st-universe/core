<?php

namespace Stu\Module\Research;

use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Research;
use Stu\Orm\Entity\Researched;

interface SelectedTechInterface
{
    public function getResearch(): Research;

    public function getResearchState(): ?Researched;

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

    /** @return array<Building> */
    public function getBuildings(): array;
}
