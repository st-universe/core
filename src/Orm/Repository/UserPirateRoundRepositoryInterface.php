<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserPirateRound;
use Stu\Orm\Entity\UserPirateRoundInterface;

/**
 * @extends ObjectRepository<UserPirateRound>
 *
 * @method null|UserPirateRoundInterface find(integer $id)
 * @method UserPirateRoundInterface[] findAll()
 */
interface UserPirateRoundRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserPirateRoundInterface;

    public function save(UserPirateRoundInterface $userPirateRound): void;

    public function delete(UserPirateRoundInterface $userPirateRound): void;

    /**
     * @return UserPirateRoundInterface[]
     */
    public function findByPirateRound(int $pirateRoundId): array;

    /**
     * @return UserPirateRoundInterface[]
     */
    public function findByUser(int $userId): array;

    public function findByUserAndPirateRound(int $userId, int $pirateRoundId): ?UserPirateRoundInterface;
}
