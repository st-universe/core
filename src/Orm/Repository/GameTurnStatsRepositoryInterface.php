<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\GameTurnStats;

/**
 * @extends ObjectRepository<GameTurnStats>
 *
 * @method GameTurnStats[] findAll()
 */
interface GameTurnStatsRepositoryInterface extends ObjectRepository
{
    public function prototype(): GameTurnStats;

    public function save(GameTurnStats $turn): void;

    public function delete(GameTurnStats $turn): void;

    public function getShipCount(): int;

    public function getShipCountManned(): int;

    public function getShipCountNpc(): int;

    public function getFlightSigs24h(): int;

    public function getFlightSigsSystem24h(): int;

    /**
     * @return array<GameTurnStats>
     */
    public function getLatestStats(int $amount, int $divisor): array;
}
