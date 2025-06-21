<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\AstronomicalEntry;
use Stu\Orm\Entity\AstronomicalEntryInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<AstronomicalEntry>
 */
final class AstroEntryRepository extends EntityRepository implements AstroEntryRepositoryInterface
{
    #[Override]
    public function prototype(): AstronomicalEntryInterface
    {
        return new AstronomicalEntry();
    }

    #[Override]
    public function save(AstronomicalEntryInterface $entry): void
    {
        $em = $this->getEntityManager();

        $em->persist($entry);
    }

    #[Override]
    public function delete(AstronomicalEntryInterface $entry): void
    {
        $em = $this->getEntityManager();

        $em->remove($entry);
    }


    #[Override]
    public function truncateAllAstroEntries(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s ae',
                AstronomicalEntry::class
            )
        )->execute();
    }

    /** @return array<AstronomicalEntryInterface> */
    #[Override]
    public function getByUser(UserInterface $user): array
    {
        return $this->findBy(
            ['user_id' => $user->getId()]
        );
    }

    /** @return array<AstronomicalEntryInterface> */
    #[Override]
    public function getByUserAndState(UserInterface $user, int $state): array
    {
        return $this->findBy(
            ['user_id' => $user->getId(), 'state' => $state]
        );
    }
}