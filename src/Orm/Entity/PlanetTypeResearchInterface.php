<?php

namespace Stu\Orm\Entity;

interface PlanetTypeResearchInterface {

    public function getId(): int;

    public function getResearch(): ResearchInterface;

    public function setResearch(ResearchInterface $research): PlanetTypeResearchInterface;

    public function getPlanetType(): PlanetTypeInterface;

    public function setPlanetType(PlanetTypeInterface $planetType): PlanetTypeResearchInterface;
}
