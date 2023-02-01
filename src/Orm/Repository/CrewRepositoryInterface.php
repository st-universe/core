<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\UserInterface;

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

    public function getAmountByUserAndShipRumpCategory(
        UserInterface $user,
        int $shipRumpCategoryId
    ): int;

    public function truncateByUser(int $userId): void;
}
