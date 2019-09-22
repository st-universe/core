<?php

namespace Stu\Module\Ship\Lib;

use ShipData;

interface ShipRemoverInterface
{

    /**
     * Destroys a ship and replaces it by a nice debrisfield
     */
    public function destroy(ShipData $ship): void;

    /**
     * Actually removes the ship entity including all references
     */
    public function remove(ShipData $ship): void;
}