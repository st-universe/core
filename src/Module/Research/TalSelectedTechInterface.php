<?php

namespace Stu\Module\Research;

use Stu\Orm\Entity\CommodityInterface;

interface TalSelectedTechInterface
{
    public function getId(): int;

    public function getName(): string;

    public function getDescription(): string;

    public function getPoints(): int;

    public function getCommodityId(): int;

    public function getUpperPlanetLimit(): int;

    public function getUpperMoonLimit(): int;

    public function getCommodity(): CommodityInterface;

    public function getResearchState();

    public function getDistinctExcludeNames(): array;

    public function hasExcludes(): bool;

    public function getDistinctPositiveDependencyNames(): array;

    public function hasPositiveDependencies(): bool;

    public function getDonePoints(): int;

    public function isResearchFinished(): bool;

    public function getStatusBar(): string;

    public function getWikiLink(): string;
}
