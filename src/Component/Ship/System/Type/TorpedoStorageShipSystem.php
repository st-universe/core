<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;

final class TorpedoStorageShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public const TORPEDO_CAPACITY = 200;

    public function activate(ShipInterface $ship, ShipSystemManagerInterface $manager): void
    {
        //passive system
    }

    public function deactivate(ShipInterface $ship): void
    {
        //passive system
    }

    public function handleDestruction(ShipInterface $ship): void
    {
        if ($ship->getTorpedoCount() > 0) {
            $ship->setIsDestroyed(true);
        }
    }
}
