<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\OrionAuction;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<OrionAuction>
 *
 * @method null|OrionAuction find(integer $id)
 */
interface OrionAuctionRepositoryInterface extends ObjectRepository
{
    public function prototype(): OrionAuction;

    public function save(OrionAuction $auction): void;

    public function delete(OrionAuction $auction): void;

    /** @return list<OrionAuction> */
    public function getActive(): array;

    /** @return list<OrionAuction> */
    public function getExpired(int $time): array;

    /** @return list<OrionAuction> */
    public function getHistorySince(int $time): array;

    /** @return list<OrionAuction> */
    public function getByWinner(User $user): array;
}
