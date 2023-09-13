<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\RpgPlotArchiv;
use Stu\Orm\Entity\RpgPlotArchivInterface;

/**
 * @extends ObjectRepository<RpgPlotArchiv>
 *
 * @method null|RpgPlotArchivInterface find(integer $id)
 * @method RpgPlotArchivInterface[] findAll()
 */
interface RpgPlotArchivRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<RpgPlotArchivInterface>
     */
    public function getByFoundingUser(int $userId): array;

    public function prototype(): RpgPlotArchivInterface;

    public function save(RpgPlotArchivInterface $rpgPlot): void;

    public function delete(RpgPlotArchivInterface $rpgPlot): void;

    /**
     * @return array<RpgPlotArchivInterface>
     */
    public function getActiveByUser(int $userId): array;

    /**
     * @return array<RpgPlotArchivInterface>
     */
    public function getEmptyOldPlots(int $maxAge): array;

    /**
     * @return array<RpgPlotArchivInterface>
     */
    public function getOrderedList(): array;

    public function truncateAllEntities(): void;
}
