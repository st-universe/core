<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipLog;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<ShipLog>
 */
interface ShipLogRepositoryInterface extends ObjectRepository
{
    public function prototype(): ShipLog;

    public function save(ShipLog $shipLog): void;

    public function delete(ShipLog $shipLog): void;

    /** @return array<int, ShipLog> */
    public function getBySpacecraftId(int $spacecraftId, bool $includePrivate = true): array;

    /** @return array<int, ShipLog> */
    public function getBySpacecraftIdUntil(int $spacecraftId, int $date, bool $includePrivate = true): array;

    public function hasVisibleLogbook(int $spacecraftId): bool;

    /** @return array<int, array{spacecraftId: int, name: string, rumpId: ?int, scanDate: ?int, logs: array<int, ShipLog>}> */
    public function getGroupedLogbooksForProfile(User $profileUser, User $visitor): array;
}
