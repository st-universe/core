<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Crew\Skill\SkillEnhancementEnum;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\SkillEnhancementLog;

/**
 * @extends EntityRepository<SkillEnhancementLog>
 */
final class SkillEnhancementLogRepository extends EntityRepository implements SkillEnhancementLogRepositoryInterface
{
    #[\Override]
    public function prototype(): SkillEnhancementLog
    {
        return new SkillEnhancementLog();
    }

    #[\Override]
    public function save(SkillEnhancementLog $log): void
    {
        $this->getEntityManager()->persist($log);
    }

    #[\Override]
    public function getForCrewman(Crew $crew): array
    {
        return $this->createQueryBuilder('log')
            ->where('log.crew_id = :crewId')
            ->setParameter('crewId', $crew->getId())
            ->orderBy('log.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    #[\Override]
    public function hasCrewExperienceSince(Crew $crew, SkillEnhancementEnum $trigger, int $timestamp): bool
    {
        return $this->createQueryBuilder('log')
            ->join('log.enhancement', 'enhancement')
            ->where('log.crew_id = :crewId')
            ->andWhere('enhancement.type = :trigger')
            ->andWhere('log.date > :timestamp')
            ->setParameter('crewId', $crew->getId())
            ->setParameter('trigger', $trigger->value)
            ->setParameter('timestamp', $timestamp)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult() !== null;
    }
}
