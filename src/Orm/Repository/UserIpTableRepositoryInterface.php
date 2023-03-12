<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserIpTable;
use Stu\Orm\Entity\UserIpTableInterface;

/**
 * @extends ObjectRepository<UserIpTable>
 */
interface UserIpTableRepositoryInterface extends ObjectRepository
{

    public function prototype(): UserIpTableInterface;

    public function save(UserIpTableInterface $userIpTable): void;

    public function findMostRecentByUser(UserInterface $user): ?UserIpTableInterface;

    public function findBySessionId(string $sessionId): ?UserIpTableInterface;
}
