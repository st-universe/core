<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PrestigeLog;
use Stu\Orm\Entity\PrestigeLogInterface;

final class PrestigeLogRepository extends EntityRepository implements PrestigeLogRepositoryInterface
{

    public function save(PrestigeLogInterface $log): void
    {
        $em = $this->getEntityManager();

        $em->persist($log);
    }

    public function delete(PrestigeLogInterface $log): void
    {
        $em = $this->getEntityManager();

        $em->remove($log);
    }

    public function prototype(): PrestigeLogInterface
    {
        return new PrestigeLog();
    }
}
