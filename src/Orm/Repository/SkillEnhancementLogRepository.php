<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\SkillEnhancementLog;
use Stu\Orm\Entity\SkillEnhancementLogInterface;

/**
 * @extends EntityRepository<SkillEnhancementLog>
 */
final class SkillEnhancementLogRepository extends EntityRepository implements SkillEnhancementLogRepositoryInterface
{

    #[Override]
    public function prototype(): SkillEnhancementLogInterface
    {
        return new SkillEnhancementLog();
    }

    #[Override]
    public function save(SkillEnhancementLogInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[Override]
    public function getForCrewman(CrewInterface $crew): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT el FROM %s el
                        WHERE el.crew_id = :crewId
                        ORDER BY el.id DESC',
                    SkillEnhancementLog::class
                )
            )
            ->setParameter('crewId', $crew->getId())
            ->getResult();
    }
}
