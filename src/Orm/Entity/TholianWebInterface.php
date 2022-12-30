<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface TholianWebInterface
{
    public function getId(): int;

    /**
     * @return ShipInterface[]|Collection
     */
    public function getCapturedShips(): Collection;
}
