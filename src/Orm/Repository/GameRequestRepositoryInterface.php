<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\GameRequestInterface;

/**
 * @extends ObjectRepository<GameRequest>
 *
 * @method GameRequestInterface[] findAll()
 */
interface GameRequestRepositoryInterface extends ObjectRepository
{
    public function prototype(): GameRequestInterface;

    public function save(GameRequestInterface $gameRequest): void;

    public function delete(GameRequestInterface $gameRequest): void;

    public function getOpenAdventDoorTriesForUser(int $userId): int;
}
