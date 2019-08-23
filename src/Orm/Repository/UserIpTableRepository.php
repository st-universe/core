<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserIpTable;
use Stu\Orm\Entity\UserIpTableInterface;

final class UserIpTableRepository extends EntityRepository implements UserIpTableRepositoryInterface
{

    public function prototype(): UserIpTableInterface
    {
        return new UserIpTable();
    }

    public function save(UserIpTableInterface $userIpTable): void
    {
        $em = $this->getEntityManager();

        $em->persist($userIpTable);
        $em->flush($userIpTable);
    }

    public function findBySessionId(string $sessionId): ?UserIpTableInterface
    {
        return $this->findOneBy([
            'session' => $sessionId
        ]);
    }
}