<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\RpgPlotMemberArchiv;
use Stu\Orm\Entity\RpgPlotMemberArchivInterface;

/**
 * @extends ObjectRepository<RpgPlotMemberArchiv>
 */
interface RpgPlotMemberArchivRepositoryInterface extends ObjectRepository
{
    public function getByPlotAndUser(int $plotId, int $userId): ?RpgPlotMemberArchivInterface;

    public function prototype(): RpgPlotMemberArchivInterface;

    public function save(RpgPlotMemberArchivInterface $rpgPlotMember): void;

    public function delete(RpgPlotMemberArchivInterface $rpgPlotMember): void;

    /**
     * @return array<RpgPlotMemberArchivInterface>
     */
    public function getByPlot(int $plotId): array;

    public function truncateAllEntities(): void;
}
