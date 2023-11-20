<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\ColonySandboxInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<ColonySandbox>
 */
final class ColonySandboxRepository extends EntityRepository implements ColonySandboxRepositoryInterface
{
    public function prototype(): ColonySandboxInterface
    {
        return new ColonySandbox();
    }

    public function save(ColonySandboxInterface $colonySandbox): void
    {
        $em = $this->getEntityManager();

        $em->persist($colonySandbox);
    }

    public function delete(ColonySandboxInterface $colonySandbox): void
    {
        $em = $this->getEntityManager();

        $em->remove($colonySandbox);
    }

    public function getByUser(UserInterface $user): array
    {
        return $this->getEntityManager()
            ->createQuery(sprintf(
                'SELECT cs
                FROM %s cs
                JOIN %s c
                WITH cs.colony = c
                WHERE c.user = :user',
                ColonySandbox::class,
                Colony::class
            ))
            ->setParameter('user', $user)
            ->getResult();
    }

    public function truncateByColony(ColonyInterface $colony): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s cs WHERE cs.colony = :colony',
                    ColonySandbox::class
                )
            )
            ->setParameters([
                'colony' => $colony
            ])
            ->execute();
    }
}
