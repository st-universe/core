<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\GameRequest;

/**
 * @extends ObjectRepository<GameRequest>
 *
 * @method GameRequest[] findAll()
 */
interface GameRequestRepositoryInterface extends ObjectRepository
{
    public function prototype(): GameRequest;

    public function save(GameRequest $gameRequest): void;

    public function delete(GameRequest $gameRequest): void;
}
