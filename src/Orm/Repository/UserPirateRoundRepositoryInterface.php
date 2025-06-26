<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserPirateRound;

/**
 * @extends ObjectRepository<UserPirateRound>
 *
 * @method null|UserPirateRound find(integer $id)
 * @method UserPirateRound[] findAll()
 */
interface UserPirateRoundRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserPirateRound;

    public function save(UserPirateRound $userPirateRound): void;

    public function delete(UserPirateRound $userPirateRound): void;

    /**
     * @return UserPirateRound[]
     */
    public function findByPirateRound(int $pirateRoundId): array;

    /**
     * @return UserPirateRound[]
     */
    public function findByUser(int $userId): array;

    public function findByUserAndPirateRound(int $userId, int $pirateRoundId): ?UserPirateRound;
}
