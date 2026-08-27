<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Crew\CrewRaceUsageEnum;
use Stu\Orm\Entity\CrewRace;
use Stu\Orm\Entity\UserCrewRace;

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
    public function getForUser(int $userId, int $factionId, CrewRaceUsageEnum $usage): array
    {
        $races = $usage === CrewRaceUsageEnum::FOREIGN_ONLY
            ? []
            : $this->getByFaction($factionId);

        if ($usage === CrewRaceUsageEnum::STANDARD) {
            return $races;
        }

        $customRaces = $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT cr FROM %s cr
                JOIN %s ucr WITH ucr.crewRace = cr
                WHERE ucr.user_id = :userId
                AND cr.creator_user_id IS NOT NULL
                AND cr.accepted = :accepted',
                CrewRace::class,
                UserCrewRace::class
            )
        )->setParameters([
            'userId' => $userId,
            'accepted' => true
        ])->getResult();

        foreach ($customRaces as $crewRace) {
            if (
                $crewRace instanceof CrewRace
                && ($crewRace->isShared() || $crewRace->getCreatorUserId() === $userId)
                && $crewRace->hasFactionId($factionId)
            ) {
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
