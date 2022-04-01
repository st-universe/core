<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\FlightSignatureInterface;

/**
 * @method null|FlightSignatureInterface find(integer $id)
 */
interface FlightSignatureRepositoryInterface extends ObjectRepository
{
    public function prototype(): FlightSignatureInterface;

    public function saveAll(array $array): void;

    public function getVisibleSignatureCount($colony): int;

    public function getSignatureRangeForUser(int $userId): array;

    public function getSignatureRangeForAlly(int $allyId): array;

    /**
     * @return FlightSignatureInterface[]
     */
    public function getVisibleSignatures($field, bool $isSystem, $ignoreId): array;

    public function deleteOldSignatures(int $threshold): void;

    public function getFlightsTop10(): array;
}
