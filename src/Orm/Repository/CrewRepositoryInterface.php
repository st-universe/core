<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<Crew>
 *
 * @method null|CrewInterface find(integer $id)
 * @method CrewInterface[] findAll()
 */
interface CrewRepositoryInterface extends ObjectRepository
{
    public function prototype(): CrewInterface;

    public function save(CrewInterface $post): void;

    public function delete(CrewInterface $post): void;

    public function getAmountByUserAndShipRumpCategory(
        UserInterface $user,
        SpacecraftRumpCategoryEnum $shipRumpCategory
    ): int;

    public function truncateByUser(int $userId): void;

    public function truncateAllCrew(): void;
}
