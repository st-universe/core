<?php

namespace Stu\Component\Spacecraft\System;

use Doctrine\Common\Collections\Collection;
use JsonMapper\JsonMapperInterface;
use Override;
use RuntimeException;
use Stu\Component\Spacecraft\System\Data\ShipSystemDataFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Spacecraft;

class SystemDataDeserializer implements SystemDataDeserializerInterface
{
    public function __construct(
        private ShipSystemDataFactoryInterface $shipSystemDataFactory,
        private JsonMapperInterface $jsonMapper
    ) {}

    #[Override]
    public function getSpecificShipSystem(
        Spacecraft $spacecraft,
        SpacecraftSystemTypeEnum $systemType,
        string $className,
        Collection $shipSystemDataCache,
        SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ): ?object {
        if (
            $systemType !== SpacecraftSystemTypeEnum::HULL
            && !$spacecraft->hasSpacecraftSystem($systemType)
        ) {
            return null;
        }

        //add system to cache if not already deserialized
        if (!$shipSystemDataCache->containsKey($systemType->value)) {
            $systemData = $this->shipSystemDataFactory->createSystemData(
                $systemType,
                $spacecraftWrapperFactory
            );
            $systemData->setSpacecraft($spacecraft);

            $data = $systemType === SpacecraftSystemTypeEnum::HULL ? null : $spacecraft->getSpacecraftSystem($systemType)->getData();

            if ($data === null) {
                $shipSystemDataCache->set($systemType->value, $systemData);
            } else {
                $shipSystemDataCache->set(
                    $systemType->value,
                    $this->jsonMapper->mapObjectFromString(
                        $data,
                        $systemData
                    )
                );
            }
        }

        //load deserialized system from cache
        $cacheItem = $shipSystemDataCache->get($systemType->value);
        if (!$cacheItem instanceof $className) {
            throw new RuntimeException('this should not happen');
        }

        return $cacheItem;
    }
}
