<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\ColonySandboxInterface;

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
}
