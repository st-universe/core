<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\GameTurnInterface;

/**
 * @method GameTurnInterface[] findAll()
 */
interface GameTurnRepositoryInterface extends ObjectRepository
{
    public function getCurrent(): GameTurnInterface;

    public function prototype(): GameTurnInterface;

    public function save(GameTurnInterface $turn): void;

    public function delete(GameTurnInterface $turn): void;
}
