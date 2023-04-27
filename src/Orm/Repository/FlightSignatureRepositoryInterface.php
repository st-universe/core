<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\FlightSignature;
use Stu\Orm\Entity\FlightSignatureInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

/**
 * @extends ObjectRepository<FlightSignature>
 *
 * @method null|FlightSignatureInterface find(integer $id)
 */
interface FlightSignatureRepositoryInterface extends ObjectRepository
{
    public function prototype(): FlightSignatureInterface;

    /**
     * @param list<FlightSignatureInterface> $array
     */
    public function saveAll(array $array): void;

    public function save(FlightSignatureInterface $item): void;

    public function getVisibleSignatureCount(ColonyInterface $colony): int;

    /**
     * @return array<array{minx: int, maxx: int, miny: int, maxy: int}>
     */
    public function getSignatureRangeForUser(int $userId): array;

    /**
     * @return array<array{minx: int, maxx: int, miny: int, maxy: int}>
     */
    public function getSignatureRangeForAlly(int $allyId): array;

    /**
     * @return array<FlightSignatureInterface>
     */
    public function getVisibleSignatures(int $fieldId, bool $isSystem, int $ignoreId): array;

    public function deleteOldSignatures(int $threshold): void;

    /**
     * @return array<array{user_id: int, sc: int, race: null|int, shipc: int}>
     */
    public function getFlightsTop10(): array;

    public function truncateAllSignatures(): void;
}
