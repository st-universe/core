<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\CrewRace;

/**
 * @extends EntityRepository<CrewRace>
 */
final class CrewRaceRepository extends EntityRepository implements CrewRaceRepositoryInterface
{
    #[\Override]
    public function prototype(): CrewRace
    {
        return new CrewRace();
    }

    #[\Override]
    public function save(CrewRace $crewRace): void
    {
        $this->getEntityManager()->persist($crewRace);
    }

    #[\Override]
    public function getByFaction(int $factionId): array
    {
        return array_values(array_filter(
            $this->findBy(['creator_user_id' => null]),
            static fn (CrewRace $crewRace): bool => $crewRace->hasFactionId($factionId)
        ));
    }

    #[\Override]
    public function getSelectableForUser(int $userId, int $factionId): array
    {
        $races = array_filter(
            $this->findAll(),
            static fn (CrewRace $crewRace): bool =>
                $crewRace->hasFactionId($factionId)
                && (!$crewRace->isCivil()
                    || ($crewRace->getCreatorUserId() !== null && $crewRace->isAccepted() && ($crewRace->isShared() || $crewRace->getCreatorUserId() === $userId)))
        );

        usort(
            $races,
            static fn (CrewRace $a, CrewRace $b): int =>
                [$a->isCivil() ? 1 : 0, $a->getDescription()] <=> [$b->isCivil() ? 1 : 0, $b->getDescription()]
        );

        return $races;
    }

    #[\Override]
    public function getStandardForFaction(int $factionId): array
    {
        $races = [];
        foreach ($this->getByFaction($factionId) as $crewRace) {
            if (!$crewRace->isCivil()) {
                $races[] = $crewRace;
            }
        }

        return $races;
    }

    #[\Override]
    public function getByCreatorUserId(int $userId): array
    {
        return $this->findBy(
            ['creator_user_id' => $userId],
            ['description' => 'ASC']
        );
    }

    #[\Override]
    public function getPendingCustomRaces(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT cr FROM %s cr
                WHERE cr.creator_user_id IS NOT NULL
                AND cr.accepted = :accepted
                AND cr.accepted_user_id IS NULL
                ORDER BY cr.creator_user_id ASC, cr.description ASC',
                CrewRace::class
            )
        )->setParameter('accepted', false)->getResult();
    }

    #[\Override]
    public function getAcceptedCustomRaces(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT cr FROM %s cr
                WHERE cr.creator_user_id IS NOT NULL
                AND cr.accepted = :accepted
                ORDER BY cr.creator_user_id ASC, cr.description ASC',
                CrewRace::class
            )
        )->setParameter('accepted', true)->getResult();
    }

    #[\Override]
    public function getRejectedCustomRaces(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT cr FROM %s cr
                WHERE cr.creator_user_id IS NOT NULL
                AND cr.accepted = :accepted
                AND cr.accepted_user_id IS NOT NULL
                ORDER BY cr.creator_user_id ASC, cr.description ASC',
                CrewRace::class
            )
        )->setParameter('accepted', false)->getResult();
    }

    #[\Override]
    public function getByGfxPath(string $gfxPath): ?CrewRace
    {
        return $this->findOneBy(['define' => $gfxPath]);
    }
}
