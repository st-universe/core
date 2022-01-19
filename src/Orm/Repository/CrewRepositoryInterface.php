<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\CrewInterface;

/**
 * @method null|CrewInterface find(integer $id)
 */
interface CrewRepositoryInterface extends ObjectRepository
{
    public function prototype(): CrewInterface;

    public function save(CrewInterface $post): void;

    public function delete(CrewInterface $post): void;

    public function getAmountByUserAndShipRumpCategory(int $userId, int $shipRumpCategoryId): int;

    public function getFreeAmountByUser(int $userId): int;

    /**
     * @return CrewInterface[]
     */
    public function getFreeByUserAndType(int $userId, int $typeId, int $maxAmount): array;

    /**
     * @return CrewInterface[]
     */
    public function getFreeByUser(int $userId, int $maxAmount): array;

    public function truncateByUser(int $userId): void;
}
