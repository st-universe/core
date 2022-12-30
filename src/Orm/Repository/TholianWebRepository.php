<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\TholianWebInterface;

final class TholianWebRepository extends EntityRepository implements TholianWebRepositoryInterface
{
    public function delete(TholianWebInterface $web): void
    {
        $em = $this->getEntityManager();

        $em->remove($web);
    }
}
