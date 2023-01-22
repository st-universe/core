<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<RpgPlot>
 *
 * @method null|RpgPlotInterface find(integer $id)
 * @method RpgPlotInterface[] findAll()
 */
interface RpgPlotRepositoryInterface extends ObjectRepository
{
    /**
     * @return RpgPlotInterface[]
     */
    public function getByFoundingUser(int $userId): array;

    public function prototype(): RpgPlotInterface;

    public function save(RpgPlotInterface $rpgPlot): void;

    public function delete(RpgPlotInterface $rpgPlot): void;

    /**
     * @return RpgPlotInterface[]
     */
    public function getActiveByUser(int $userId): array;

    /**
     * @return RpgPlotInterface[]
     */
    public function getByUser(UserInterface $user): array;

    /**
     * @return RpgPlotInterface[]
     */
    public function getEmptyOldPlots(int $maxAge): array;

    /**
     * @return RpgPlotInterface[]
     */
    public function getOrderedList(): array;
}
