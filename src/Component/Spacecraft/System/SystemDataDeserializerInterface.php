<?php

namespace Stu\Component\Spacecraft\System;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Spacecraft\System\Data\AbstractSystemData;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface SystemDataDeserializerInterface
{
    /**
     * @template T
     * @param class-string<T> $className
     * @param Collection<int, AbstractSystemData> $shipSystemDataCache
     * @return T|null
     */
    public function getSpecificShipSystem(
        SpacecraftInterface $spacecraft,
        SpacecraftSystemTypeEnum $systemType,
        string $className,
        Collection $shipSystemDataCache,
        SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    );
}
