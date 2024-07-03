<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\AstronomicalEntry;
use Stu\Orm\Entity\AstronomicalEntryInterface;
use Stu\Orm\Entity\ShipInterface;

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
    public function getByShipLocation(ShipInterface $ship, bool $showOverSystem = true): ?AstronomicalEntryInterface
    {
        $system = $ship->getSystem();
        if ($system === null && $showOverSystem) {
            $system = $ship->isOverSystem();
        }
        $mapRegion = $system === null ? $ship->getMapRegion() : null;

        return $this->findOneBy(
            [
                'user_id' => $ship->getUser()->getId(),
                'systems_id' => $system === null ? null : $system->getId(),
                'region_id' => $mapRegion === null ? null : $mapRegion->getId()
            ]
        );
    }

    #[Override]
    public function save(AstronomicalEntryInterface $entry): void
    {
        $em = $this->getEntityManager();

        $em->persist($entry);
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
}
