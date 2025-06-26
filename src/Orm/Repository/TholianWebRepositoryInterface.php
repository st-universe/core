<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\TholianWeb;

/**
 * @extends ObjectRepository<TholianWeb>
 *
 * @method null|TholianWeb find(integer $id)
 */
interface TholianWebRepositoryInterface extends ObjectRepository
{
    public function save(TholianWeb $web): void;

    public function delete(TholianWeb $web): void;

    public function getWebAtLocation(Ship $ship): ?TholianWeb;

    /**
     * @return list<TholianWeb>
     */
    public function getFinishedWebs(): array;
}
