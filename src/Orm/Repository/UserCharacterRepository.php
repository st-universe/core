<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\UserCharacter;
use Stu\Orm\Entity\UserCharacterInterface;

/**
 * @extends EntityRepository<UserCharacter>
 */
final class UserCharacterRepository extends EntityRepository implements UserCharacterRepositoryInterface
{
    #[Override]
    public function prototype(): UserCharacterInterface
    {
        return new UserCharacter();
    }

    #[Override]
    public function save(UserCharacterInterface $userCharacters): void
    {
        $em = $this->getEntityManager();

        $em->persist($userCharacters);
    }

    #[Override]
    public function delete(UserCharacterInterface $userCharacters): void
    {
        $em = $this->getEntityManager();

        $em->remove($userCharacters);
    }

    /**
     * @return list<UserCharacterInterface>
     */
    #[Override]
    public function findByUserId(int $userId): array
    {
        return $this->findBy(['user' => $userId], ['id' => 'ASC']);
    }
}
