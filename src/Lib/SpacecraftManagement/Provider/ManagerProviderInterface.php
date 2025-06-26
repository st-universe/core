<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Provider;

use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\User;

interface ManagerProviderInterface
{
    public function getUser(): User;

    public function getEps(): int;

    public function lowerEps(int $amount): ManagerProviderInterface;

    public function getName(): string;

    public function getSectorString(): string;

    public function getFreeCrewAmount(): int;

    public function addCrewAssignment(Spacecraft $spacecraft, int $amount): void;

    public function getFreeCrewStorage(): int;

    /**
     * @param array<CrewAssignment> $crewAssignments
     */
    public function addCrewAssignments(array $crewAssignments): void;

    /**
     * @return Collection<int, Storage>
     */
    public function getStorage(): Collection;

    public function upperStorage(Commodity $commodity, int $amount): void;

    public function lowerStorage(Commodity $commodity, int $amount): void;
}
