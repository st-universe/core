<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\RpgPlotMember;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<RpgPlotMember>
 */
interface RpgPlotMemberRepositoryInterface extends ObjectRepository
{
    public function getByPlotAndUser(int $plotId, int $userId): ?RpgPlotMember;

    public function prototype(): RpgPlotMember;

    public function save(RpgPlotMember $rpgPlotMember): void;

    public function delete(RpgPlotMember $rpgPlotMember): void;

    /**
     * @return array<RpgPlotMember>
     */
    public function getByUser(User $user): array;

    /**
     * @return array<RpgPlotMember>
     */
    public function getByPlot(int $plotId): array;

    public function truncateAllEntities(): void;
}
