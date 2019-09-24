<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperComputer;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperEnergyWeapon;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperEps;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperHull;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperImpulseDrive;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperInterface;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperProjectileWeapon;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperShield;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperSpecial;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperWarpcore;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

final class ShipCreator implements ShipCreatorInterface
{
    private $buildplanModuleRepository;

    private $shipSystemRepository;

    private $shipRepository;

    public function __construct(
        BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        ShipSystemRepositoryInterface $shipSystemRepository,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->buildplanModuleRepository = $buildplanModuleRepository;
        $this->shipSystemRepository = $shipSystemRepository;
        $this->shipRepository = $shipRepository;
    }

    public function createBy(int $userId, int $shipRumpId, int $shipBuildplanId, ?ColonyInterface $colony = null): ShipInterface
    {
        $ship = $this->shipRepository->prototype();
        $ship->setUserId($userId);
        $ship->setBuildplanId($shipBuildplanId);
        $ship->setRumpId($shipRumpId);

        $moduleTypeList = [
            MODULE_TYPE_HULL => function (ShipInterface $ship): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperHull($ship->getRump(), $ship->getBuildplan()->getModulesByType(MODULE_TYPE_HULL));
            },
            MODULE_TYPE_SHIELDS => function (ShipInterface $ship): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperShield($ship->getRump(), $ship->getBuildplan()->getModulesByType(MODULE_TYPE_SHIELDS));
            },
            MODULE_TYPE_EPS => function (ShipInterface $ship): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperEps($ship->getRump(), $ship->getBuildplan()->getModulesByType(MODULE_TYPE_EPS));
            },
            MODULE_TYPE_IMPULSEDRIVE => function (ShipInterface $ship): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperImpulseDrive($ship->getRump(), $ship->getBuildplan()->getModulesByType(MODULE_TYPE_IMPULSEDRIVE));
            },
            MODULE_TYPE_WARPCORE => function (ShipInterface $ship): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperWarpcore($ship->getRump(), $ship->getBuildplan()->getModulesByType(MODULE_TYPE_WARPCORE));
            },
            MODULE_TYPE_COMPUTER => function (ShipInterface $ship): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperComputer($ship->getRump(), $ship->getBuildplan()->getModulesByType(MODULE_TYPE_COMPUTER));
            },
            MODULE_TYPE_PHASER => function (ShipInterface $ship): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperEnergyWeapon($ship->getRump(), $ship->getBuildplan()->getModulesByType(MODULE_TYPE_PHASER));
            },
            MODULE_TYPE_TORPEDO => function (ShipInterface $ship): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperProjectileWeapon($ship->getRump(), $ship->getBuildplan()->getModulesByType(MODULE_TYPE_TORPEDO));
            },
            MODULE_TYPE_SPECIAL => function (ShipInterface $ship): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperSpecial($ship->getRump(), $ship->getBuildplan()->getModulesByType(MODULE_TYPE_SPECIAL));
            },
        ];

        foreach ($moduleTypeList as $moduleTypeId => $wrapperCallable) {
            $buildplanModules = $ship->getBuildplan()->getModulesByType($moduleTypeId);
            if ($buildplanModules !== []) {
                /** @var ModuleRumpWrapperInterface $wrapper */
                $wrapper = $wrapperCallable($ship);
                $wrapper->apply($ship);
            }
        }

        $ship->setMaxEbatt((int)round($ship->getMaxEps() / 3));
        $ship->setName($ship->getRump()->getName());
        $ship->setSensorRange($ship->getRump()->getBaseSensorRange());
        $ship->setAlertState(ALERT_GREEN);

        $this->shipRepository->save($ship);
        if ($colony) {
            $ship->setSX($colony->getSX());
            $ship->setSY($colony->getSY());
            $ship->setSystemsId($colony->getSystemsId());
            $ship->setCX($colony->getSystem()->getCX());
            $ship->setCY($colony->getSystem()->getCY());
            $this->shipRepository->save($ship);
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