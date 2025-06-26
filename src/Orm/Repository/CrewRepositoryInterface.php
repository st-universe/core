<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<Crew>
 *
 * @method null|Crew find(integer $id)
 * @method Crew[] findAll()
 */
interface CrewRepositoryInterface extends ObjectRepository
{
    public function prototype(): Crew;

    public function save(Crew $post): void;

    public function delete(Crew $post): void;

    public function getAmountByUserAndShipRumpCategory(
        User $user,
        SpacecraftRumpCategoryEnum $shipRumpCategory
    ): int;

    public function truncateByUser(int $userId): void;

    public function truncateAllCrew(): void;
}
