<?php

namespace Stu\Component\Ship\System;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Ship\System\Data\AbstractSystemData;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\ShipInterface;

interface SystemDataDeserializerInterface
{
    /**
     * @template T
     * @param class-string<T> $className
     * @param Collection<int, AbstractSystemData> $shipSystemDataCache
     * @return T|null
     */
    public function getSpecificShipSystem(
        ShipInterface $ship,
        ShipSystemTypeEnum $systemType,
        string $className,
        Collection $shipSystemDataCache,
        ShipWrapperFactoryInterface $shipWrapperFactory
    );
}
