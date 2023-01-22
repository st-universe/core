<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Award;
use Stu\Orm\Entity\AwardInterface;

/**
 * @extends ObjectRepository<Award>
 *
 * @method null|AwardInterface find(integer $id)
 */
interface AwardRepositoryInterface extends ObjectRepository
{
    public function save(AwardInterface $award): void;

    public function delete(AwardInterface $award): void;

    public function prototype(): AwardInterface;
}
