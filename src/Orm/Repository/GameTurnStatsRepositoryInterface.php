<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\GameTurnStatsInterface;

/**
 * @method GameTurnStatsInterface[] findAll()
 */
interface GameTurnStatsRepositoryInterface extends ObjectRepository
{
    public function prototype(): GameTurnStatsInterface;

    public function save(GameTurnStatsInterface $turn): void;

    public function delete(GameTurnStatsInterface $turn): void;
}
