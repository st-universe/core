<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\CrewInterface;

interface CrewRepositoryInterface extends ObjectRepository
{
    public function prototype(): CrewInterface;

    public function save(CrewInterface $post): void;

    public function delete(CrewInterface $post): void;

    public function getAmountByUserAndShipRumpCategory(int $userId, int $shipRumpCategoryId): int;

    public function getFreeAmountByUser(int $userId): int;

    public function getFreeByUserAndType(int $userId, int $typeId): ?CrewInterface;

    public function getFreeByUser(int $userId): ?CrewInterface;

    public function truncateByUser(int $userId): void;
}