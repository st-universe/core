<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<RpgPlot>
 *
 * @method null|RpgPlot find(integer $id)
 * @method RpgPlot[] findAll()
 */
interface RpgPlotRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<RpgPlot>
     */
    public function getByFoundingUser(int $userId): array;

    public function prototype(): RpgPlot;

    public function save(RpgPlot $rpgPlot): void;

    public function delete(RpgPlot $rpgPlot): void;

    /**
     * @return array<RpgPlot>
     */
    public function getActiveByUser(int $userId): array;

    /**
     * @return array<RpgPlot>
     */
    public function getByUser(User $user): array;

    /**
     * @return array<RpgPlot>
     */
    public function getEmptyOldPlots(int $maxAge): array;

    /**
     * @return array<RpgPlot>
     */
    public function getOrderedList(): array;
}
