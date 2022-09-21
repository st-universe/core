<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Stu\Module\Control\EntityManagerLoggingInterface;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\GameRequestInterface;

final class GameRequestRepository
{
    public function prototype(): GameRequestInterface
    {
        return new GameRequest();
    }

    public function save(EntityManagerLoggingInterface $entityManager, GameRequestInterface $gameRequest): void
    {
        $entityManager->persist($gameRequest);
    }
}
