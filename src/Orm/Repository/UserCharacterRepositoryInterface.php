<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserCharacter;
use Stu\Orm\Entity\UserCharacterInterface;

/**
 * @extends ObjectRepository<UserCharacter>
 *
 * @method null|UserCharacterInterface find(integer $id)
 */
interface UserCharacterRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserCharacterInterface;

    public function save(UserCharacterInterface $userCharacters): void;

    public function delete(UserCharacterInterface $userCharacters): void;

    /**
     * @return list<UserCharacterInterface>
     */
    public function findByUserId(int $userId): array;
}
