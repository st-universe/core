<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserCharacter;

/**
 * @extends EntityRepository<UserCharacter>
 */
final class UserCharacterRepository extends EntityRepository implements UserCharacterRepositoryInterface
{
    #[\Override]
    public function prototype(): UserCharacter
    {
        return new UserCharacter();
    }

    #[\Override]
    public function save(UserCharacter $userCharacters): void
    {
        $em = $this->getEntityManager();

        $em->persist($userCharacters);
    }

    #[\Override]
    public function delete(UserCharacter $userCharacters): void
    {
        $em = $this->getEntityManager();

        $em->remove($userCharacters);
    }

    /**
     * @return list<UserCharacter>
     */
    #[\Override]
    public function findByUserId(int $userId): array
    {
        return $this->findBy(['user' => $userId], ['id' => 'ASC']);
    }
}
