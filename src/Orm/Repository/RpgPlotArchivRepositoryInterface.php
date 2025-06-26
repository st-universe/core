<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\RpgPlotArchiv;

/**
 * @extends ObjectRepository<RpgPlotArchiv>
 *
 * @method null|RpgPlotArchiv find(integer $id)
 * @method RpgPlotArchiv[] findAll()
 */
interface RpgPlotArchivRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<RpgPlotArchiv>
     */
    public function getByFoundingUser(int $userId): array;

    public function prototype(): RpgPlotArchiv;

    public function save(RpgPlotArchiv $rpgPlot): void;

    public function delete(RpgPlotArchiv $rpgPlot): void;

    /**
     * @return array<RpgPlotArchiv>
     */
    public function getActiveByUser(int $userId): array;

    /**
     * @return array<RpgPlotArchiv>
     */
    public function getEmptyOldPlots(int $maxAge): array;

    /**
     * @return array<RpgPlotArchiv>
     */
    public function getOrderedList(): array;

    public function truncateAllEntities(): void;
}
