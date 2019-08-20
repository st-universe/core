<?php

namespace Stu\Module\Research;

use GoodData;

interface TalSelectedTechInterface
{
    public function getId(): int;

    public function getName(): string;

    public function getDescription(): string;

    public function getPoints(): int;

    public function getGoodId(): int;

    public function getUpperPlanetLimit(): int;

    public function getUpperMoonLimit(): int;

    public function getGood(): GoodData;

    public function getResearchState();

    public function getExcludes(): array;

    public function hasExcludes(): bool;

    public function getPositiveDependencies(): array;

    public function hasPositiveDependencies(): bool;

    public function getDonePoints(): int;

    public function isResearchFinished(): bool;
}