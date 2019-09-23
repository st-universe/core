<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ShipInterface;

interface ShipRemoverInterface
{

    /**
     * Destroys a ship and replaces it by a nice debrisfield
     */
    public function destroy(ShipInterface $ship): void;

    /**
     * Actually removes the ship entity including all references
     */
    public function remove(ShipInterface $ship): void;
}