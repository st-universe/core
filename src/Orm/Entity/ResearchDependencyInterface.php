<?php

namespace Stu\Orm\Entity;

interface ResearchDependencyInterface
{
    public function getId(): int;

    public function getResearchId(): int;

    public function setResearchId(int $researchId): ResearchDependencyInterface;

    public function getDependsOn(): int;

    public function setDependsOn(int $dependsOn): ResearchDependencyInterface;

    public function getMode(): int;

    public function setMode(int $mode): ResearchDependencyInterface;

    public function getResearch(): ResearchInterface;

    public function getResearchDependOn(): ResearchInterface;
}
