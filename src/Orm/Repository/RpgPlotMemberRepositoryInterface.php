<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\RpgPlotMember;
use Stu\Orm\Entity\RpgPlotMemberInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<RpgPlotMember>
 */
interface RpgPlotMemberRepositoryInterface extends ObjectRepository
{
    public function getByPlotAndUser(int $plotId, int $userId): ?RpgPlotMemberInterface;

    public function prototype(): RpgPlotMemberInterface;

    public function save(RpgPlotMemberInterface $rpgPlotMember): void;

    public function delete(RpgPlotMemberInterface $rpgPlotMember): void;

    /**
     * @return list<RpgPlotMemberInterface>
     */
    public function getByUser(UserInterface $user): array;

    /**
     * @return list<RpgPlotMemberInterface>
     */
    public function getByPlot(int $plotId): array;
}
