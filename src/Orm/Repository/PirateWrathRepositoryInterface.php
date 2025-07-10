<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PirateWrath;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<PirateWrath>
 *
 * @method PirateWrath[] findAll()
 */
interface PirateWrathRepositoryInterface extends ObjectRepository
{
    public function save(PirateWrath $wrath): void;

    public function delete(PirateWrath $wrath): void;

    public function prototype(): PirateWrath;

    /**
     * @return PirateWrath[]
     */
    public function getPirateWrathTop10(): array;

    /**
     * @return PirateWrath[]
     */
    public function getByUser(User $user): array;
}
