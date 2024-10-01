<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\ShipModule\ModuleSpecialAbilityEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ModuleSpecialRepositoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\BuildplanModuleInterface;


final class ShipRetrofit implements ShipRetrofitInterface
{
    private ShipSystemRepositoryInterface $shipSystemRepository;
    private BuildplanModuleRepositoryInterface $buildplanModuleRepository;
    private ModuleSpecialRepositoryInterface $moduleSpecialRepository;
    private ShipWrapperFactoryInterface $shipWrapperFactory;
    private ColonyStorageManagerInterface $colonyStorageManager;
    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipSystemRepositoryInterface $shipSystemRepository,
        BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        ModuleSpecialRepositoryInterface $moduleSpecialRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        ColonyStorageManagerInterface $colonyStorageManager,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipSystemRepository = $shipSystemRepository;
        $this->buildplanModuleRepository = $buildplanModuleRepository;
        $this->moduleSpecialRepository = $moduleSpecialRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function updateBy(ShipInterface $ship, ShipBuildplanInterface $newBuildplan, ColonyInterface $colony): void
    {
        $oldBuildplan = $ship->getBuildplan();
        $wrapper = $this->shipWrapperFactory->wrapShip($ship);
        $returnedmodules = [];

        if ($oldBuildplan === null) {
            return;
        }


        foreach (ShipModuleTypeEnum::cases() as $moduleType) {
            $oldModules = $this->buildplanModuleRepository->getByBuildplanAndModuleType($oldBuildplan->getId(), $moduleType->value);
            $newModules = $this->buildplanModuleRepository->getByBuildplanAndModuleType($newBuildplan->getId(), $moduleType->value);

            $addingModules = array_udiff($newModules, $oldModules, function ($a, $b) {
                return $a->getModule()->getId() - $b->getModule()->getId();
            });

            $deletingModules = array_udiff($oldModules, $newModules, function ($a, $b) {
                return $a->getModule()->getId() - $b->getModule()->getId();
            });

            if (!empty($addingModules)) {
                $systems = [];
                $this->addModuleSystems($addingModules, $systems);
                foreach ($systems as $systemType => $module) {
                    $this->createShipSystem($systemType, $ship, $module);
                    $moduleRumpWrapper = $moduleType->getModuleRumpWrapperCallable()($ship->getRump(), $newBuildplan);
                    $moduleRumpWrapper->apply($wrapper);
                }
            }

            foreach ($deletingModules as $oldModule) {
                $system = $this->shipSystemRepository->getByShipAndModule($ship->getId(), $oldModule->getModule()->getId());
                if ($system !== null) {
                    if ($system->getStatus() >= 100) {
                        if (mt_rand(1, 100) <= 25) {
                            $returnedmodules[] = $system->getModule();
                        }
                        $this->shipSystemRepository->delete($system);
                    }
                }
            }
        }

        if (!empty($returnedmodules)) {
            $msg = "
            Die folgenden Module wurden durch den Umbau zurückgewonnen: ";
            foreach ($returnedmodules as $module) {
                if ($module != null) {
                    $this->colonyStorageManager->upperStorage($colony, $module->getCommodity(), 1);
                    $msg .= $module->getName() . ", ";
                }
            }
            $msg = rtrim($msg, ", ");
        } else {
            $msg = null;
        }

        $txt = _("Auf der Kolonie " . $colony->getName() . " wurde die " . $ship->getName() . " umgerüstet");

        if ($msg !== null) {
            $txt .= '. ' . $msg;
        }

        $this->privateMessageSender->send(
            UserEnum::USER_NOONE,
            $colony->getUserId(),
            $txt,
            PrivateMessageFolderTypeEnum::SPECIAL_COLONY
        );

        $ship->setBuildplan($newBuildplan);
    }

    /**
     * @param array<BuildplanModuleInterface> $modules
     * @param array<int, ModuleInterface|null> $systems
     */
    private function addModuleSystems(array $modules, array &$systems): void
    {
        foreach ($modules as $buildplanmodule) {
            $module = $buildplanmodule->getModule();

            $systemType = $module->getSystemType();
            if (
                $systemType === null
                && $module->getType()->hasCorrespondingSystemType()
            ) {
                $systemType = $module->getType()->getSystemType();
            }

            if ($systemType !== null) {
                $systems[$systemType->value] = $module;
            }

            switch ($module->getType()) {
                case ShipModuleTypeEnum::SPECIAL:
                    $this->addSpecialSystems($module, $systems);
                    break;
            }
        }
    }

    /**
     * @param array<int, null|ModuleInterface> $systems
     */
    private function addSpecialSystems(ModuleInterface $module, array &$systems): void
    {
        $moduleSpecials = $this->moduleSpecialRepository->getByModule($module->getId());

        foreach ($moduleSpecials as $special) {
            switch ($special->getSpecialId()) {
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_CLOAK:
                    $systems[ShipSystemTypeEnum::SYSTEM_CLOAK->value] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_TACHYON_SCANNER:
                    $systems[ShipSystemTypeEnum::SYSTEM_TACHYON_SCANNER->value] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_TROOP_QUARTERS:
                    $systems[ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS->value] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_ASTRO_LABORATORY:
                    $systems[ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY->value] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_SUBSPACE_FIELD_SENSOR:
                    $systems[ShipSystemTypeEnum::SYSTEM_SUBSPACE_SCANNER->value] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_MATRIX_SENSOR:
                    $systems[ShipSystemTypeEnum::SYSTEM_MATRIX_SCANNER->value] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_TORPEDO_STORAGE:
                    $systems[ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE->value] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_SHUTTLE_RAMP:
                    $systems[ShipSystemTypeEnum::SYSTEM_SHUTTLE_RAMP->value] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_TRANSWARP_COIL:
                    $systems[ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL->value] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_HIROGEN_TRACKER:
                    $systems[ShipSystemTypeEnum::SYSTEM_TRACKER->value] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_THOLIAN_WEB:
                    $systems[ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB->value] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_BUSSARD_COLLECTOR:
                    $systems[ShipSystemTypeEnum::SYSTEM_BUSSARD_COLLECTOR->value] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_RPG:
                    $systems[ShipSystemTypeEnum::SYSTEM_RPG_MODULE->value] = null;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_AGGREGATION_SYSTEM:
                    $systems[ShipSystemTypeEnum::SYSTEM_AGGREGATION_SYSTEM->value] = $module;
                    break;
            }
        }
    }


    private function createShipSystem(int $systemType, ShipInterface $ship, ?ModuleInterface $module): void
    {
        $shipSystem = $this->shipSystemRepository->prototype();
        $shipSystem->setShip($ship);
        $ship->getSystems()->set($systemType, $shipSystem);
        $shipSystem->setSystemType(ShipSystemTypeEnum::from($systemType));
        if ($module !== null) {
            $shipSystem->setModule($module);
        }
        $shipSystem->setStatus(100);
        $shipSystem->setMode(ShipSystemModeEnum::MODE_OFF);

        $this->shipSystemRepository->save($shipSystem);
    }
}