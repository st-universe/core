<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\CrewAssignmentInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<CrewAssignment>
 *
 * @method null|CrewAssignmentInterface find(integer $id)
 */
interface CrewAssignmentRepositoryInterface extends ObjectRepository
{
    public function prototype(): CrewAssignmentInterface;

    public function save(CrewAssignmentInterface $post): void;

    public function delete(CrewAssignmentInterface $post): void;

    public function getAmountBySpacecraft(SpacecraftInterface $spacecraft): int;

    public function hasEnoughCrew(SpacecraftInterface $spacecraft): bool;

    public function hasCrewmanOfUser(SpacecraftInterface $spacecraft, int $userId): bool;

    //VIA LOCATION
    /**
     * @return array<CrewAssignmentInterface>
     */
    public function getByUserAtColonies(UserInterface $user): array;

    /**
     * @return array<CrewAssignmentInterface>
     */
    public function getByUserOnEscapePods(UserInterface $user): array;

    /**
     * @return array<CrewAssignmentInterface>
     */
    public function getByUserAtTradeposts(UserInterface $user): array;

    //AMOUNT
    public function getAmountByUser(UserInterface $user): int;

    public function getAmountByUserAtTradeposts(UserInterface $user): int;

    public function getAmountByUserOnColonies(UserInterface $user): int;

    public function getAmountByUserOnShips(UserInterface $user): int;

    /**
     * @return array<array{user_id: int, factionid: int, crewc: int}>
     */
    public function getCrewsTop10(): array;

    /**
     * @return array<array{id: int, name: string, sector: string, amount: int}>
     */
    public function getOrphanedSummaryByUserAtTradeposts(int $userId): array;

    public function truncateBySpacecraft(SpacecraftInterface $spacecraft): void;

    public function truncateByUser(UserInterface $user): void;

    public function hasCrewOnForeignStation(UserInterface $user): bool;
}
