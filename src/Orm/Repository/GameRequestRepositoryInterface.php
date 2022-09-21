<?php

namespace Stu\Orm\Repository;

use Stu\Module\Control\EntityManagerLoggingInterface;
use Stu\Orm\Entity\GameRequestInterface;

interface GameRequestRepositoryInterface
{
    public function prototype(): GameRequestInterface;

    public function save(EntityManagerLoggingInterface $entityManager, GameRequestInterface $gameRequest): void;
}
