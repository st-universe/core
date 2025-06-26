<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\GameTurn;

/**
 * @extends ObjectRepository<GameTurn>
 *
 * @method GameTurn[] findAll()
 */
interface GameTurnRepositoryInterface extends ObjectRepository
{
    public function getCurrent(): ?GameTurn;

    public function prototype(): GameTurn;

    public function save(GameTurn $turn): void;

    public function delete(GameTurn $turn): void;

    public function truncateAllGameTurns(): void;
}
