<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\FlightSignature;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<FlightSignature>
 *
 * @method null|FlightSignature find(integer $id)
 */
interface FlightSignatureRepositoryInterface extends ObjectRepository
{
    public function prototype(): FlightSignature;

    /**
     * @param list<FlightSignature> $array
     */
    public function saveAll(array $array): void;

    public function save(FlightSignature $item): void;

    public function getVisibleSignatureCount(Colony $colony): int;

    /**
     * @return array<array{minx: int, maxx: int, miny: int, maxy: int}>
     */
    public function getSignatureRange(): array;

    /**
     * @return array<array{minx: int, maxx: int, miny: int, maxy: int}>
     */
    public function getSignatureRangeForShip(int $shipId): array;

    /**
     * @return array<array{minx: int, maxx: int, miny: int, maxy: int}>
     */
    public function getSignatureRangeForUser(int $userId): array;

    /**
     * @return array<array{minx: int, maxx: int, miny: int, maxy: int}>
     */
    public function getSignatureRangeForAlly(int $allyId): array;

    /**
     * @return array<FlightSignature>
     */
    public function getVisibleSignatures(int $locationId, int $ignoreId): array;

    public function deleteOldSignatures(int $threshold): void;

    /**
     * @return array<array{user_id: int, sc: int, factionid: null|int, shipc: int}>
     */
    public function getFlightsTop10(): array;

    public function getSignaturesForUser(User $user): int;
}
