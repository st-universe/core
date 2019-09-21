<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use ShipData;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

final class ShipCreator implements ShipCreatorInterface
{
    private $buildplanModuleRepository;

    private $shipSystemRepository;

    public function __construct(
        BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        ShipSystemRepositoryInterface $shipSystemRepository
    ) {
        $this->buildplanModuleRepository = $buildplanModuleRepository;
        $this->shipSystemRepository = $shipSystemRepository;
    }

    public function createBy(int $userId, int $shipRumpId, int $shipBuildplanId, ?ColonyInterface $colony = null): ShipData
    {
        $ship = new ShipData();
        $ship->setUserId($userId);
        $ship->setBuildplanId($shipBuildplanId);
        $ship->setRumpId($shipRumpId);
        for ($i = 1; $i <= MODULE_TYPE_COUNT; $i++) {
            if ($ship->getBuildplan()->getModulesByType($i)) {
                $class = '\Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapper' . $i;
                $wrapper = new $class($ship->getRump(), $ship->getBuildplan()->getModulesByType($i));
                foreach ($wrapper->getCallbacks() as $callback => $value) {
                    $ship->$callback($value);
                }
            }
        }
        $ship->setMaxEbatt(round($ship->getMaxEps() / 3));
        $ship->setName($ship->getRump()->getName());
        $ship->setSensorRange($ship->getRump()->getBaseSensorRange());
        $ship->save();
        if ($colony) {
            $ship->setSX($colony->getSX());
            $ship->setSY($colony->getSY());
            $ship->setSystemsId($colony->getSystemsId());
            $ship->setCX($colony->getSystem()->getCX(), true);
            $ship->setCY($colony->getSystem()->getCY(), true);
            $ship->save();
        }

        $this->createByModuleList(
            $ship->getId(),
            $this->buildplanModuleRepository->getByBuildplan((int)$ship->getBuildplanId())
        );

        return $ship;
    }

    private function createByModuleList(int $shipId, array $modules): void
    {
        $systems = array();
        foreach ($modules as $key => $module) {
            switch ($module->getModule()->getType()) {
                case MODULE_TYPE_SHIELDS:
                    $systems[SYSTEM_SHIELDS] = $module->getModule();
                    break;
                case MODULE_TYPE_EPS:
                    $systems[SYSTEM_EPS] = $module->getModule();
                    break;
                case MODULE_TYPE_IMPULSEDRIVE:
                    $systems[SYSTEM_IMPULSEDRIVE] = $module->getModule();
                    break;
                case MODULE_TYPE_WARPCORE:
                    $systems[SYSTEM_WARPCORE] = $module->getModule();
                    $systems[SYSTEM_WARPDRIVE] = $module->getModule();
                    break;
                case MODULE_TYPE_COMPUTER:
                    $systems[SYSTEM_COMPUTER] = $module->getModule();
                    $systems[SYSTEM_LSS] = 0;
                    $systems[SYSTEM_NBS] = 0;
                    break;
                case MODULE_TYPE_PHASER:
                    $systems[SYSTEM_PHASER] = $module->getModule();
                    break;
                case MODULE_TYPE_TORPEDO:
                    $systems[SYSTEM_TORPEDO] = $module->getModule();
                    break;
                case MODULE_TYPE_SPECIAL:
                    // XXX: TBD
                    break;
            }
        }
        foreach ($systems as $sysId => $module) {
            $obj = $this->shipSystemRepository->prototype();
            $obj->setShipId((int) $shipId);
            $obj->setSystemType((int) $sysId);
            if ($module !== 0) {
                $obj->setModule($module);
            }
            $obj->setStatus(100);

            $this->shipSystemRepository->save($obj);
        }
    }
}