<?php

namespace Stu\Orm\Entity;

interface ColonyClassResearchInterface
{

    public function getId(): int;

    public function getResearch(): ResearchInterface;

    public function setResearch(ResearchInterface $research): ColonyClassResearchInterface;

    public function getColonyClass(): ColonyClassInterface;

    public function setColonyClass(ColonyClassInterface $colonyClass): ColonyClassResearchInterface;
}
