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
     * @param array<FlightSignatureInterface> $array
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
     * @param MapInterface|StarSystemMapInterface $field
     *
     * @return FlightSignatureInterface[]
     */
    public function getVisibleSignatures($field, bool $isSystem, int $ignoreId): array;

    public function deleteOldSignatures(int $threshold): void;

    /**
     * @return array<array{user_id: int, sc: int, race: int, shipc: int}>
     */
    public function getFlightsTop10(): array;
}
