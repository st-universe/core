<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\CrewAssignmentInterface;
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

    /**
     * @return list<CrewAssignmentInterface>
     */
    public function getByShip(int $shipId): array;

    /**
     * @return list<CrewAssignmentInterface>
     */
    public function getByShipAndSlot(int $shipId, int $slotId): array;

    //VIA LOCATION
    /**
     * @return list<CrewAssignmentInterface>
     */
    public function getByUserAtColonies(int $userId): array;

    /**
     * @return list<CrewAssignmentInterface>
     */
    public function getByUserOnEscapePods(int $userId): array;

    /**
     * @return list<CrewAssignmentInterface>
     */
    public function getByUserAtTradeposts(int $userId): array;

    //AMOUNT
    public function getAmountByUser(UserInterface $user): int;

    public function getAmountByUserAtTradeposts(UserInterface $user): int;

    public function getAmountByUserOnColonies(int $userId): int;

    public function getAmountByUserOnShips(UserInterface $user): int;

    /**
     * @return array<array{user_id: int, factionid: int, crewc: int}>
     */
    public function getCrewsTop10(): array;

    /**
     * @return array<array{id: int, name: string, sector: string, amount: int}>
     */
    public function getOrphanedSummaryByUserAtTradeposts(int $userId): array;

    public function truncateByShip(int $shipId): void;

    public function truncateByUser(int $userId): void;

    public function hasCrewOnForeignStation(UserInterface $user): bool;
}
