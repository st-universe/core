<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\UserCharacters;
use Stu\Orm\Entity\UserCharactersInterface;

/**
 * @extends EntityRepository<UserCharacters>
 */
final class UserCharactersRepository extends EntityRepository implements UserCharactersRepositoryInterface
{
    #[Override]
    public function prototype(): UserCharactersInterface
    {
        return new UserCharacters();
    }

    #[Override]
    public function save(UserCharactersInterface $userCharacters): void
    {
        $em = $this->getEntityManager();

        $em->persist($userCharacters);
    }

    #[Override]
    public function delete(UserCharactersInterface $userCharacters): void
    {
        $em = $this->getEntityManager();

        $em->remove($userCharacters);
    }

    /**
     * @return list<UserCharactersInterface>
     */
    #[Override]
    public function findByUserId(int $userId): array
    {
        return $this->findBy(['user' => $userId], ['id' => 'ASC']);
    }
}
