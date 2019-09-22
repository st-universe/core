<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Ship;

use Ship;
use Stu\Module\Ship\Lib\ShipRemoverInterface;

final class ShipTickManager implements ShipTickManagerInterface
{
    private $shipRemover;

    private $shipTick;

    public function __construct(
        ShipRemoverInterface $shipRemover,
        ShipTickInterface $shipTick
    ) {
        $this->shipRemover = $shipRemover;
        $this->shipTick = $shipTick;
    }

    public function work(): void
    {
        $ships = Ship::getObjectsBy('WHERE user_id IN (SELECT id FROM stu_user WHERE id > 100) AND plans_id > 0');

        foreach ($ships as $ship) {
            //echo "Processing Ship ".$ship->getId()." at ".microtime()."\n";
            $this->shipTick->work($ship);
        }
        $this->handleNPCShips();
        $this->lowerTrumfieldHuell();
    }

    private function lowerTrumfieldHuell(): void
    {
        foreach (Ship::getObjectsBy('WHERE user_id=' . USER_NOONE . ' AND rumps_id IN (SELECT id FROM stu_rumps WHERE category_id=' . SHIP_CATEGORY_DEBRISFIELD . ')') as $ship) {
            $lower = rand(5, 15);
            if ($ship->getHuell() <= $lower) {
                $this->shipRemover->remove($ship);
                continue;
            }
            $ship->lowerHuell($lower);
            $ship->save();
        }
    }

    private function handleNPCShips(): void
    {
        // @todo
        foreach (Ship::getObjectsBy('WHERE user_id IN (SELECT id FROM stu_user where id!=' . USER_NOONE . ' AND id < 100)') as $ship) {
            $eps = ceil($ship->getMaxEps() / 10);
            if ($eps + $ship->getEps() > $ship->getMaxEps()) {
                $eps = $ship->getMaxEps() - $ship->getEps();
            }
            $ship->upperEps($eps);
            $ship->save();
        }
    }
}