<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewInterface;

/**
 * @extends ObjectRepository<Crew>
 *
 * @method null|CrewInterface find(integer $id)
 */
interface CrewRepositoryInterface extends ObjectRepository
{
    public function prototype(): CrewInterface;

    public function save(CrewInterface $post): void;

    public function delete(CrewInterface $post): void;

    public function getAmountByUserAndShipRumpCategory(int $userId, int $shipRumpCategoryId): int;

    public function truncateByUser(int $userId): void;
}
