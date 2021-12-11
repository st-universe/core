<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ShipInterface;

interface ShipLoaderInterface
{
    public function getByIdAndUser(int $shipId, int $userId, bool $allowUplink = false): ShipInterface;

    public function find(int $shipId): ?ShipInterface;

    public function save(ShipInterface $ship): void;
}
