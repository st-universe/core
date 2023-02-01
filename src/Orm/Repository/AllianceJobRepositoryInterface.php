<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\AllianceJobInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<AllianceJob>
 *
 * @method null|AllianceJobInterface find(integer $id)
 */
interface AllianceJobRepositoryInterface extends ObjectRepository
{
    public function prototype(): AllianceJobInterface;

    public function save(AllianceJobInterface $post): void;

    public function delete(AllianceJobInterface $post): void;

    /**
     * @return AllianceJobInterface[]
     */
    public function getByUser(int $userId): array;

    /**
     * @return AllianceJobInterface[]
     */
    public function getByAlliance(int $allianceId): array;

    public function truncateByUser(int $userId): void;

    public function truncateByAlliance(int $allianceId): void;

    /**
     * @return AllianceJobInterface[]
     */
    public function getByAllianceAndType(int $allianceId, int $typeId): array;

    public function getByUserAndAllianceAndType(
        UserInterface $user,
        AllianceInterface $alliance,
        int $type
    ): ?AllianceJobInterface;

    public function getSingleResultByAllianceAndType(int $allianceId, int $typeId): ?AllianceJobInterface;
}
