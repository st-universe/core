<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
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
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShipCreator implements ShipCreatorInterface
{
    private BuildplanModuleRepositoryInterface $buildplanModuleRepository;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    private ShipRepositoryInterface $shipRepository;

    private UserRepositoryInterface $userRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    public function __construct(
        BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        ShipSystemRepositoryInterface $shipSystemRepository,
        ShipRepositoryInterface $shipRepository,
        UserRepositoryInterface $userRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository
    ) {
        $this->buildplanModuleRepository = $buildplanModuleRepository;
        $this->shipSystemRepository = $shipSystemRepository;
        $this->shipRepository = $shipRepository;
        $this->userRepository = $userRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
    }

    public function createBy(int $userId, int $shipRumpId, int $shipBuildplanId, ?ColonyInterface $colony = null): ShipInterface
    {
        $ship = $this->shipRepository->prototype();
        $ship->setUser($this->userRepository->find($userId));
        $ship->setBuildplan($this->shipBuildplanRepository->find($shipBuildplanId));
        $ship->setRump($this->shipRumpRepository->find($shipRumpId));

        $moduleTypeList = [
            ShipModuleTypeEnum::MODULE_TYPE_HULL => function (ShipInterface $ship): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperHull($ship->getRump(), $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_HULL));
            },
            ShipModuleTypeEnum::MODULE_TYPE_SHIELDS => function (ShipInterface $ship): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperShield($ship->getRump(), $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_SHIELDS));
            },
            ShipModuleTypeEnum::MODULE_TYPE_EPS => function (ShipInterface $ship): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperEps($ship->getRump(), $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_EPS));
            },
            ShipModuleTypeEnum::MODULE_TYPE_IMPULSEDRIVE => function (ShipInterface $ship): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperImpulseDrive($ship->getRump(), $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_IMPULSEDRIVE));
            },
            ShipModuleTypeEnum::MODULE_TYPE_WARPCORE => function (ShipInterface $ship): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperWarpcore($ship->getRump(), $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_WARPCORE));
            },
            ShipModuleTypeEnum::MODULE_TYPE_COMPUTER => function (ShipInterface $ship): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperComputer($ship->getRump(), $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_COMPUTER));
            },
            ShipModuleTypeEnum::MODULE_TYPE_PHASER => function (ShipInterface $ship): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperEnergyWeapon($ship->getRump(), $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_PHASER));
            },
            ShipModuleTypeEnum::MODULE_TYPE_TORPEDO => function (ShipInterface $ship): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperProjectileWeapon($ship->getRump(), $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_TORPEDO));
            },
            ShipModuleTypeEnum::MODULE_TYPE_SPECIAL => function (ShipInterface $ship): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperSpecial($ship->getRump(), $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_SPECIAL));
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
        $ship->setAlertState(ShipAlertStateEnum::ALERT_GREEN);

        $this->shipRepository->save($ship);
        if ($colony) {
            $ship->setSX($colony->getSX());
            $ship->setSY($colony->getSY());
            $ship->setSystem($colony->getSystem());
            $ship->setCX($colony->getSystem()->getCX());
            $ship->setCY($colony->getSystem()->getCY());
            $this->shipRepository->save($ship);
        }

        $this->createByModuleList(
            $ship,
            $this->buildplanModuleRepository->getByBuildplan($ship->getBuildplan()->getId())
        );

        return $ship;
    }

    private function createByModuleList(ShipInterface $ship, array $modules): void
    {
        $systems = array();
        foreach ($modules as $key => $module) {
            switch ($module->getModule()->getType()) {
                case ShipModuleTypeEnum::MODULE_TYPE_SHIELDS:
                    $systems[ShipSystemTypeEnum::SYSTEM_SHIELDS] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_EPS:
                    $systems[ShipSystemTypeEnum::SYSTEM_EPS] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_IMPULSEDRIVE:
                    $systems[ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_WARPCORE:
                    $systems[ShipSystemTypeEnum::SYSTEM_WARPCORE] = $module->getModule();
                    $systems[ShipSystemTypeEnum::SYSTEM_WARPDRIVE] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_COMPUTER:
                    $systems[ShipSystemTypeEnum::SYSTEM_COMPUTER] = $module->getModule();
                    $systems[ShipSystemTypeEnum::SYSTEM_LSS] = 0;
                    $systems[ShipSystemTypeEnum::SYSTEM_NBS] = 0;
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_PHASER:
                    $systems[ShipSystemTypeEnum::SYSTEM_PHASER] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_TORPEDO:
                    $systems[ShipSystemTypeEnum::SYSTEM_TORPEDO] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_SPECIAL:
                    // XXX: TBD
                    break;
            }
        }
        foreach ($systems as $sysId => $module) {
            $obj = $this->shipSystemRepository->prototype();
            $obj->setShip($ship);
            $obj->setSystemType((int) $sysId);
            if ($module !== 0) {
                $obj->setModule($module);
            }
            $obj->setStatus(100);

            $this->shipSystemRepository->save($obj);
        }
    }
}
