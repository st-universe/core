<?php

namespace Stu\Component\Ship\System;

use Override;
use Doctrine\Common\Collections\Collection;
use JsonMapper\JsonMapperInterface;
use RuntimeException;
use Stu\Component\Ship\System\Data\ShipSystemDataFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\ShipInterface;

class SystemDataDeserializer implements SystemDataDeserializerInterface
{

    public function __construct(
        private ShipSystemDataFactoryInterface $shipSystemDataFactory,
        private JsonMapperInterface $jsonMapper
    ) {
    }

    #[Override]
    public function getSpecificShipSystem(
        ShipInterface $ship,
        ShipSystemTypeEnum $systemType,
        string $className,
        Collection $shipSystemDataCache,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        if (
            $systemType !== ShipSystemTypeEnum::SYSTEM_HULL
            && !$ship->hasShipSystem($systemType)
        ) {
            return null;
        }

        //add system to cache if not already deserialized
        if (!$shipSystemDataCache->containsKey($systemType->value)) {
            $systemData = $this->shipSystemDataFactory->createSystemData(
                $systemType,
                $shipWrapperFactory
            );
            $systemData->setShip($ship);

            $data = $systemType === ShipSystemTypeEnum::SYSTEM_HULL ? null : $ship->getShipSystem($systemType)->getData();

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
