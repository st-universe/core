<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserCharacter;

/**
 * @extends ObjectRepository<UserCharacter>
 *
 * @method null|UserCharacter find(integer $id)
 */
interface UserCharacterRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserCharacter;

    public function save(UserCharacter $userCharacters): void;

    public function delete(UserCharacter $userCharacters): void;

    /**
     * @return list<UserCharacter>
     */
    public function findByUserId(int $userId): array;
}
