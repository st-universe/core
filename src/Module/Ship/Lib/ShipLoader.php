<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use AccessViolation;
use Ship;
use ShipData;

final class ShipLoader implements ShipLoaderInterface
{

    public function getById($shipId): ShipData
    {
        return Ship::getById($shipId);
    }

    public function getByIdAndUser(int $shipId, int $userId): ShipData
    {
        $ship = $this->getById($shipId);

        if ($ship->getUserId() != $userId) {
            throw new AccessViolation();
        }

        return $ship;
    }
}