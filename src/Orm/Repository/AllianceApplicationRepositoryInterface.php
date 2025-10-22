<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceApplication;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<AllianceApplication>
 */
interface AllianceApplicationRepositoryInterface extends ObjectRepository
{
    public function prototype(): AllianceApplication;

    public function save(AllianceApplication $application): void;

    public function delete(AllianceApplication $application): void;

    /**
     * @return array<AllianceApplication>
     */
    public function getByAlliance(int $allianceId): array;

    /**
     * @return array<AllianceApplication>
     */
    public function getByUser(int $userId): array;

    public function getByUserAndAlliance(User $user, Alliance $alliance): ?AllianceApplication;

    public function truncateByUser(int $userId): void;

    public function truncateByAlliance(int $allianceId): void;
}
