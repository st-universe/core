<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<AllianceJob>
 *
 * @method null|AllianceJob find(integer $id)
 */
interface AllianceJobRepositoryInterface extends ObjectRepository
{
    public function prototype(): AllianceJob;

    public function save(AllianceJob $post): void;

    public function delete(AllianceJob $post): void;

    /**
     * @return array<int, AllianceJob>
     */
    public function getByUser(int $userId): array;

    /**
     * @return AllianceJob[]
     */
    public function getByAlliance(int $allianceId): array;

    public function truncateByUser(int $userId): void;

    public function truncateByAlliance(int $allianceId): void;

    /**
     * @return AllianceJob[]
     */
    public function getByAllianceAndType(int $allianceId, int $typeId): array;

    public function getByUserAndAllianceAndType(
        User $user,
        Alliance $alliance,
        int $type
    ): ?AllianceJob;

    public function getSingleResultByAllianceAndType(int $allianceId, int $typeId): ?AllianceJob;
}
