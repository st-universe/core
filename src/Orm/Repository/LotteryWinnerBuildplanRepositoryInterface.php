<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\LotteryWinnerBuildplan;

/**
 * @extends ObjectRepository<LotteryWinnerBuildplan>
 *
 * @method null|LotteryWinnerBuildplan find(integer $id)
 */
interface LotteryWinnerBuildplanRepositoryInterface extends ObjectRepository
{
    public function prototype(): LotteryWinnerBuildplan;

    public function save(LotteryWinnerBuildplan $lotteryWinnerBuildplan): void;

    /**
     * @return LotteryWinnerBuildplan[]
     */
    public function findByFactionId(?int $factionId): array;
}
