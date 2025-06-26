<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Award;

/**
 * @extends ObjectRepository<Award>
 *
 * @method null|Award find(integer $id)
 */
interface AwardRepositoryInterface extends ObjectRepository
{
    public function save(Award $award): void;

    public function delete(Award $award): void;

    public function prototype(): Award;
}
