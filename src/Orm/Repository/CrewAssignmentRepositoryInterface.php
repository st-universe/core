<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<CrewAssignment>
 *
 * @method null|CrewAssignment find(integer $id)
 */
interface CrewAssignmentRepositoryInterface extends ObjectRepository
{
    public function prototype(): CrewAssignment;

    public function save(CrewAssignment $post): void;

    public function delete(CrewAssignment $post): void;

    public function getAmountBySpacecraft(Spacecraft $spacecraft): int;

    public function hasEnoughCrew(Spacecraft $spacecraft): bool;

    public function hasCrewmanOfUser(Spacecraft $spacecraft, int $userId): bool;

    //VIA LOCATION
    /**
     * @return array<CrewAssignment>
     */
    public function getByUserAtColonies(User $user): array;

    /**
     * @return array<CrewAssignment>
     */
    public function getByUserOnEscapePods(User $user): array;

    /**
     * @return array<CrewAssignment>
     */
    public function getByUserAtTradeposts(User $user): array;

    //AMOUNT
    public function getAmountByUser(User $user): int;

    public function getAmountByUserAtTradeposts(User $user): int;

    public function getAmountByUserOnColonies(User $user): int;

    public function getAmountByUserOnShips(User $user): int;

    /**
     * @return array<array{user_id: int, factionid: int, crewc: int}>
     */
    public function getCrewsTop10(): array;

    /**
     * @return array<array{id: int, name: string, sector: string, amount: int}>
     */
    public function getOrphanedSummaryByUserAtTradeposts(int $userId): array;

    public function truncateBySpacecraft(Spacecraft $spacecraft): void;

    public function truncateByUser(User $user): void;

    public function hasCrewOnForeignStation(User $user): bool;
}
