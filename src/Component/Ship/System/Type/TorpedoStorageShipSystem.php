<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Override;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class TorpedoStorageShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public const int TORPEDO_CAPACITY = 200;

    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE;
    }

    #[Override]
    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void
    {
        //passive system
    }

    #[Override]
    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        //passive system
    }

    #[Override]
    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        //TODO should destroy whole ship, not only setting field
        $ship = $wrapper->get();
        if ($ship->getTorpedoCount() > 0) {
            $ship->setIsDestroyed(true);
        }
    }
}
