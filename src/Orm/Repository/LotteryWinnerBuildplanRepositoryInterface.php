<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\LotteryWinnerBuildplan;
use Stu\Orm\Entity\LotteryWinnerBuildplanInterface;

/**
 * @extends ObjectRepository<LotteryWinnerBuildplan>
 *
 * @method null|LotteryWinnerBuildplanInterface find(integer $id)
 */
interface LotteryWinnerBuildplanRepositoryInterface extends ObjectRepository
{
    public function prototype(): LotteryWinnerBuildplanInterface;

    public function save(LotteryWinnerBuildplanInterface $lotteryWinnerBuildplan): void;
}
