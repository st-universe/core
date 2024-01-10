<?php

namespace Stu\Orm\Entity;

use Stu\Component\Research\ResearchModeEnum;

interface ResearchDependencyInterface
{
    public function getId(): int;

    public function getResearchId(): int;

    public function setResearchId(int $researchId): ResearchDependencyInterface;

    public function getDependsOn(): int;

    public function setDependsOn(int $dependsOn): ResearchDependencyInterface;

    public function getMode(): ResearchModeEnum;

    public function setMode(ResearchModeEnum $mode): ResearchDependencyInterface;

    public function getResearch(): ResearchInterface;

    public function getResearchDependOn(): ResearchInterface;
}
