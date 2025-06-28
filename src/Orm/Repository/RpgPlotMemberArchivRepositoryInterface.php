<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\RpgPlotMemberArchiv;

/**
 * @extends ObjectRepository<RpgPlotMemberArchiv>
 */
interface RpgPlotMemberArchivRepositoryInterface extends ObjectRepository
{
    public function getByPlotAndUser(int $plotId, int $userId): ?RpgPlotMemberArchiv;

    public function prototype(): RpgPlotMemberArchiv;

    public function save(RpgPlotMemberArchiv $rpgPlotMember): void;

    public function delete(RpgPlotMemberArchiv $rpgPlotMember): void;

    /**
     * @return array<RpgPlotMemberArchiv>
     */
    public function getByPlot(int $plotId): array;

    public function truncateAllEntities(): void;

    /**
     * @return array<RpgPlotMemberArchiv>
     */
    public function getByPlotFormerId(int $plotFormerId): array;
}
