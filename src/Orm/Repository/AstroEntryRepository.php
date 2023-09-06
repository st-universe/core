<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\AstronomicalEntry;
use Stu\Orm\Entity\AstronomicalEntryInterface;

/**
 * @extends EntityRepository<AstronomicalEntry>
 */
final class AstroEntryRepository extends EntityRepository implements AstroEntryRepositoryInterface
{
    public function prototype(): AstronomicalEntryInterface
    {
        return new AstronomicalEntry();
    }

    public function getByUserAndSystem(int $userId, ?int $starSystemId): ?AstronomicalEntryInterface
    {
        return $this->findOneBy(
            [
                'user_id' => $userId,
                'systems_id' => $starSystemId
            ]
        );
    }

    public function getByUserAndRegion(int $userId, ?int $regionId): ?AstronomicalEntryInterface
    {
        return $this->findOneBy(
            [
                'user_id' => $userId,
                'region_id' => $regionId
            ]
        );
    }

    public function save(AstronomicalEntryInterface $entry): void
    {
        $em = $this->getEntityManager();

        $em->persist($entry);
    }

    public function truncateAllAstroEntries(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s ae',
                AstronomicalEntry::class
            )
        )->execute();
    }
}
