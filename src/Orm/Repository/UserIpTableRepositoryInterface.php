<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserIpTableInterface;

interface UserIpTableRepositoryInterface extends ObjectRepository
{

    public function prototype(): UserIpTableInterface;

    public function save(UserIpTableInterface $userIpTable): void;

    public function findBySessionId(string $sessionId): ?UserIpTableInterface;
}