<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use RuntimeException;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\ShipModule\ModuleSpecialAbilityEnum;
use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ConstructionProgressInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ModuleSpecialRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShipCreator implements ShipCreatorInterface
{
    private BuildplanModuleRepositoryInterface $buildplanModuleRepository;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    private ShipRepositoryInterface $shipRepository;

    private UserRepositoryInterface $userRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ModuleSpecialRepositoryInterface $moduleSpecialRepository;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private ShipConfiguratorFactoryInterface $shipConfiguratorFactory;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        ShipSystemRepositoryInterface $shipSystemRepository,
        ShipRepositoryInterface $shipRepository,
        UserRepositoryInterface $userRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ModuleSpecialRepositoryInterface $moduleSpecialRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        ShipConfiguratorFactoryInterface $shipConfiguratorFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->buildplanModuleRepository = $buildplanModuleRepository;
        $this->shipSystemRepository = $shipSystemRepository;
        $this->shipRepository = $shipRepository;
        $this->userRepository = $userRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->moduleSpecialRepository = $moduleSpecialRepository;
        $this->starSystemMapRepository = $starSystemMapRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->shipConfiguratorFactory = $shipConfiguratorFactory;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function createBy(
        int $userId,
        int $shipRumpId,
        int $shipBuildplanId,
        ?ColonyInterface $colony = null,
        ?ConstructionProgressInterface $progress = null
    ): ShipConfiguratorInterface {
        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new RuntimeException('user not existent');
        }

        $rump = $this->shipRumpRepository->find($shipRumpId);
        if ($rump === null) {
            throw new RuntimeException('rump not existent');
        }

        $buildplan = $this->shipBuildplanRepository->find($shipBuildplanId);
        if ($buildplan === null) {
            throw new RuntimeException('buildplan not existent');
        }

        $ship = $progress !== null ? $progress->getShip() : $this->shipRepository->prototype();
        $ship->setUser($user);
        $ship->setBuildplan($buildplan);
        $ship->setRump($rump);
        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);

        if ($ship->getRump()->getCategoryId() === ShipRumpEnum::SHIP_CATEGORY_STATION) {
            $ship->setSpacecraftType(SpacecraftTypeEnum::SPACECRAFT_TYPE_STATION);
        }

        //create ship systems
        $this->createShipSystemsByModuleList(
            $ship,
            $this->buildplanModuleRepository->getByBuildplan(
                $buildplan->getId()
            ),
            $progress
        );

        $wrapper = $this->shipWrapperFactory->wrapShip($ship);

        foreach (ShipModuleTypeEnum::cases() as $moduleType) {

            $moduleTypeId = $moduleType->value;

            if ($this->loggerUtil->doLog()) {
                $this->loggerUtil->log(sprintf("moduleTypeId: %d", $moduleTypeId));
            }
            $buildplanModules = $buildplan->getModulesByType($moduleType);
            if ($buildplanModules !== []) {
                if ($this->loggerUtil->doLog()) {
                    $this->loggerUtil->log("wrapperCallable!");
                }
                $moduleRumpWrapper = $moduleType->getModuleRumpWrapperCallable()($rump, $buildplan);
                $moduleRumpWrapper->apply($wrapper);
            }
        }

        if ($ship->getName() == '' || $ship->getName() === sprintf('%s in Bau', $ship->getRump()->getName())) {
            $ship->setName($ship->getRump()->getName());
        }

        $ship->setAlertStateGreen();

        $this->shipRepository->save($ship);
        if ($colony !== null) {
            $starsystemMap = $this->starSystemMapRepository->getByCoordinates($colony->getSystem()->getId(), $colony->getSx(), $colony->getSy());

            $ship->setCx($colony->getSystem()->getCx());
            $ship->setCy($colony->getSystem()->getCy());
            $ship->setStarsystemMap($starsystemMap);
            $this->shipRepository->save($ship);
        }

        return $this->shipConfiguratorFactory->createShipConfigurator($wrapper);
    }

    /**
     * @param array<BuildplanModuleInterface> $modules
     */
    private function createShipSystemsByModuleList(
        ShipInterface $ship,
        array $modules,
        ?ConstructionProgressInterface $progress
    ): void {
        $systems = [];

        //default systems, that almost every ship should have
        if ($ship->getRump()->getCategoryId() !== ShipRumpEnum::SHIP_CATEGORY_SHUTTLE) {
            $systems[ShipSystemTypeEnum::SYSTEM_DEFLECTOR->value] = 0;
            $systems[ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM->value] = 0;
        }
        $systems[ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT->value] = 0;
        //TODO transporter

        if ($ship->getRump()->getCategoryId() === ShipRumpEnum::SHIP_CATEGORY_STATION) {
            $systems[ShipSystemTypeEnum::SYSTEM_BEAM_BLOCKER->value] = 0;
        }

        if ($ship->getRump()->isShipyard()) {
            $systems[ShipSystemTypeEnum::SYSTEM_CONSTRUCTION_HUB->value] = 0;
        }

        if ($ship->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_SENSOR) {
            $systems[ShipSystemTypeEnum::SYSTEM_UPLINK->value] = 0;
        }

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
                case ShipModuleTypeEnum::SENSOR:
                    $systems[ShipSystemTypeEnum::SYSTEM_NBS->value] = 0;
                    break;
                case ShipModuleTypeEnum::SPECIAL:
                    $this->addSpecialSystems($module, $systems);
                    break;
            }
        }
        if ($progress !== null) {
            foreach ($progress->getSpecialModules() as $mod) {
                $this->addSpecialSystems($mod->getModule(), $systems);
            }
        }
        foreach ($systems as $systemType => $module) {
            $obj = $this->shipSystemRepository->prototype();
            $obj->setShip($ship);
            $ship->getSystems()->set($systemType, $obj);
            $obj->setSystemType(ShipSystemTypeEnum::from($systemType));
            if ($module !== 0) {
                $obj->setModule($module);
            }
            $obj->setStatus(100);
            $obj->setMode(ShipSystemModeEnum::MODE_OFF);

            $this->shipSystemRepository->save($obj);
        }
    }

    /**
     * @param array<int, ModuleInterface> $systems
     */
    private function addSpecialSystems(ModuleInterface $module, &$systems): void
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
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_RPG:
                    $systems[ShipSystemTypeEnum::SYSTEM_RPG_MODULE->value] = 0;
                    break;
            }
        }
    }
}
