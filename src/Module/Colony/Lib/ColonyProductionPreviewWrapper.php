<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Orm\Entity\BuildingInterface;

class ColonyProductionPreviewWrapper
{
    /**
     * @param array<ColonyProduction> $production
     */
    public function __construct(private ColonyLibFactoryInterface $colonyLibFactory, private BuildingInterface $building, private array $production)
    {
    }

    /** @return array<ColonyProduction> */
    public function getPreview(): array
    {
        $bcommodities = $this->building->getCommodities();

        $ret = [];
        foreach ($bcommodities as $commodityId => $prod) {
            $commodityId = $prod->getCommodityId();
            if (array_key_exists($commodityId, $this->production)) {

                $ret[$commodityId] = clone $this->production[$commodityId];
                $ret[$commodityId]->upperProduction($prod->getAmount());
            } else {

                $obj = $this->colonyLibFactory->createColonyProduction(
                    $prod->getCommodity(),
                    $prod->getAmount()
                );
                $ret[$commodityId] = $obj;
            }

            $ret[$commodityId]->setPreviewProduction($prod->getAmount());
        }

        return $ret;
    }
}
