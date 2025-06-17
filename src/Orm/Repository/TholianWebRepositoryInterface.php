<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TholianWeb;
use Stu\Orm\Entity\TholianWebInterface;

/**
 * @extends ObjectRepository<TholianWeb>
 *
 * @method null|TholianWebInterface find(integer $id)
 */
interface TholianWebRepositoryInterface extends ObjectRepository
{
    public function save(TholianWebInterface $web): void;

    public function delete(TholianWebInterface $web): void;

    public function getWebAtLocation(ShipInterface $ship): ?TholianWebInterface;

    /**
     * @return list<TholianWebInterface>
     */
    public function getFinishedWebs(): array;
}
