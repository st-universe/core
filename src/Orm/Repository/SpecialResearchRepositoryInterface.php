<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\SpecialResearch;
use Stu\Orm\Entity\User;

interface SpecialResearchRepositoryInterface
{
    /** @return list<SpecialResearch> */
    public function getByUser(User $user): array;

    /** @return list<SpecialResearch> */
    public function getRunning(): array;

    /** @return list<SpecialResearch> */
    public function getRunningByField(PlanetField $field): array;

    public function lockStorage(Colony $colony, PlanetField $field): void;

    public function save(SpecialResearch $research): void;

    public function deleteByUser(User $user): void;

    public function detachColony(Colony $colony): void;
}
