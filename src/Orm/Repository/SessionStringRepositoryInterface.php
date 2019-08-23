<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\SessionStringInterface;

interface SessionStringRepositoryInterface extends ObjectRepository
{

    public function isValid(string $sessionString, int $userId): bool;

    public function truncate(int $userId): void;

    public function prototype(): SessionStringInterface;

    public function save(SessionStringInterface $sessionString): void;
}