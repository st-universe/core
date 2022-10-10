<?php

declare(strict_types=1);

namespace Stu\Lib;

use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;

class ColonyProductionPreviewWrapper
{
    private $production = null;

    function __construct(&$production)
    {
        $this->production = $production;
    }


    function __get($buildingId)
    {
        return $this->getPreview($buildingId);
    }

    private function getPreview($buildingId)
    {
        // @todo refactor
        global $container;

        $bcommodities = $container->get(BuildingCommodityRepositoryInterface::class)->getByBuilding((int)$buildingId);
        $ret = [];
        foreach ($bcommodities as $commodityId => $prod) {
            $commodityId = $prod->getCommodityId();
            if (array_key_exists($commodityId, $this->production)) {
                $ret[$commodityId] = clone $this->production[$commodityId];
                $ret[$commodityId]->upperProduction($prod->getAmount());
            } else {
                $obj = new ColonyProduction();
                $obj->setCommodityId($commodityId);
                $obj->setProduction($prod->getAmount());
                $ret[$commodityId] = $obj;
            }
            $ret[$commodityId]->setPreviewProduction($prod->getAmount());
        }
        return $ret;
    }
}
