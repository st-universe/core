<?php

namespace Stu\Module\Tick\Spacecraft\Handler;

use Override;
use Stu\Component\Faction\FactionEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Commodity\Lib\CommodityCacheInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

class AggregationSystemHandler implements SpacecraftTickHandlerInterface
{
    public function __construct(
        private CommodityCacheInterface $commodityCache,
        private StorageRepositoryInterface $storageRepository,
        private StorageManagerInterface $storageManager
    ) {}

    #[Override]
    public function handleSpacecraftTick(
        SpacecraftWrapperInterface $wrapper,
        InformationInterface $information
    ): void {

        if (!$wrapper instanceof StationWrapperInterface) {
            return;
        }

        $station = $wrapper->get();
        $aggsys = $wrapper->getAggregationSystemSystemData();

        if ($aggsys === null) {
            return;
        } else {
            $module = $station->getSpacecraftSystem(SpacecraftSystemTypeEnum::AGGREGATION_SYSTEM)->getModule();
            $producedAmount = 0;
            $usedAmount = 0;
            $usedCommodity = null;
            $producedCommodity = null;


            if ($module !== null) {
                $commodity = $aggsys->getCommodityId();
                $commodities = CommodityTypeConstants::COMMODITY_CONVERSIONS;

                if ($commodity > 0) {
                    foreach ($commodities as $entry) {
                        if ($entry[0] === $commodity) {
                            $producedCommodityId = $entry[1];
                            $producedCommodity = $this->commodityCache->get($producedCommodityId);
                            $usedCommodity = $this->commodityCache->get($entry[0]);
                            $usedAmount = $entry[2];
                            $producedAmount = $entry[3];
                            break;
                        }
                    }

                    if ($module->getFactionId() == FactionEnum::FACTION_FERENGI) {
                        $producedAmount *= 2;
                        $usedAmount *= 2;
                    }
                    $storage = $this->storageRepository->findOneBy([
                        'commodity' => $usedCommodity,
                        'spacecraft' => $station
                    ]);
                    if (!$storage && $usedCommodity) {
                        $information->addInformationf('Es ist kein %s vorhanden!', $usedCommodity->getName());
                    }

                    if ($storage && $producedCommodity && $usedCommodity) {
                        if ($storage->getAmount() >= $usedAmount) {
                            $this->storageManager->lowerStorage(
                                $station,
                                $usedCommodity,
                                $usedAmount
                            );
                            $this->storageManager->upperStorage(
                                $station,
                                $producedCommodity,
                                $producedAmount
                            );
                        } else {
                            $information->addInformationf('Nicht genügend %s vorhanden!', $usedCommodity->getName());
                        }
                    }
                }
            }
        }
    }
}
