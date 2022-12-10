<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\DealsAuctionInterface;


/**
 * @method null|DealsAuctionInterface find(integer $id)
 */
interface DealsAuctionRepositoryInterface extends ObjectRepository
{
    public function prototype(): DealsAuctionInterface;

    public function save(DealsAuctionInterface $post): void;

    public function delete(DealsAuctionInterface $post): void;
}