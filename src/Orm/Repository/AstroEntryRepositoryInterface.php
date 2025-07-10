<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AstronomicalEntry;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<AstronomicalEntry>
 *
 * @method null|AstronomicalEntry find(integer $id)
 * @method AstronomicalEntry[] findAll()
 */
interface AstroEntryRepositoryInterface extends ObjectRepository
{
    public function prototype(): AstronomicalEntry;

    public function save(AstronomicalEntry $entry): void;

    public function delete(AstronomicalEntry $entry): void;

    /**
     * @return array<AstronomicalEntry>
     */
    public function getByUser(User $user): array;

    /**
     * @return array<AstronomicalEntry>
     */
    public function getByUserAndState(User $user, int $state): array;
}
