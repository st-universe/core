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

    public function getByUserAndSystem($userId, $starSystemId): ?AstronomicalEntryInterface
    {
        return $this->findOneBy(
            [
                'user_id' => $userId,
                'systems_id' => $starSystemId
            ]
        );
    }

    public function save(AstronomicalEntryInterface $entry): void
    {
        $em = $this->getEntityManager();

        $em->persist($entry);
    }
}
