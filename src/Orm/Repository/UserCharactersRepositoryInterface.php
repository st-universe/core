<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserCharacters;
use Stu\Orm\Entity\UserCharactersInterface;

/**
 * @extends ObjectRepository<UserCharacters>
 *
 * @method null|UserCharactersInterface find(integer $id)
 */
interface UserCharactersRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserCharactersInterface;

    public function save(UserCharactersInterface $userCharacters): void;

    public function delete(UserCharactersInterface $userCharacters): void;

    /**
     * @return list<UserCharactersInterface>
     */
    public function findByUserId(int $userId): array;
}
