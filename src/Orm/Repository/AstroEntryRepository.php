<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\AstronomicalEntry;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<AstronomicalEntry>
 */
final class AstroEntryRepository extends EntityRepository implements AstroEntryRepositoryInterface
{
    #[\Override]
    public function prototype(): AstronomicalEntry
    {
        return new AstronomicalEntry();
    }

    #[\Override]
    public function save(AstronomicalEntry $entry): void
    {
        $em = $this->getEntityManager();

        $em->persist($entry);
    }

    #[\Override]
    public function delete(AstronomicalEntry $entry): void
    {
        $em = $this->getEntityManager();

        $em->remove($entry);
        $em->flush(); //TODO really neccessary?
    }

    /** @return array<AstronomicalEntry> */
    #[\Override]
    public function getByUser(User $user): array
    {
        return $this->findBy(
            ['user_id' => $user->getId()]
        );
    }

    /** @return array<AstronomicalEntry> */
    #[\Override]
    public function getByUserAndState(User $user, int $state): array
    {
        return $this->findBy(
            ['user_id' => $user->getId(), 'state' => $state]
        );
    }
}
