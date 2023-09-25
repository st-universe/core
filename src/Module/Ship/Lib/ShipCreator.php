<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use RuntimeException;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperComputer;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperEnergyWeapon;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperEps;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperHull;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperImpulseDrive;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperInterface;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperProjectileWeapon;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperReactor;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperShield;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperSpecial;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperWarpcore;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperWarpDrive;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperSensor;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\ShipModule\ModuleSpecialAbilityEnum;
use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ConstructionProgressInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
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
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function createBy(
        int $userId,
        int $shipRumpId,
        int $shipBuildplanId,
        ?ColonyInterface $colony = null,
        ?ConstructionProgressInterface $progress = null
    ): ShipWrapperInterface {
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

        //create ship systems
        $this->createShipSystemsByModuleList(
            $ship,
            $this->buildplanModuleRepository->getByBuildplan(
                $buildplan->getId()
            ),
            $progress
        );

        $moduleTypeList = [
            ShipModuleTypeEnum::MODULE_TYPE_HULL => fn (ShipBuildplanInterface $buildplan, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface => new ModuleRumpWrapperHull($wrapper, $buildplan->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_HULL)),
            ShipModuleTypeEnum::MODULE_TYPE_SHIELDS => fn (ShipBuildplanInterface $buildplan, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface => new ModuleRumpWrapperShield($wrapper, $buildplan->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_SHIELDS)),
            ShipModuleTypeEnum::MODULE_TYPE_EPS => fn (ShipBuildplanInterface $buildplan, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface => new ModuleRumpWrapperEps($wrapper, $buildplan->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_EPS)),
            ShipModuleTypeEnum::MODULE_TYPE_IMPULSEDRIVE => fn (ShipBuildplanInterface $buildplan, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface => new ModuleRumpWrapperImpulseDrive($wrapper, $buildplan->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_IMPULSEDRIVE)),
            ShipModuleTypeEnum::MODULE_TYPE_WARPCORE => fn (ShipBuildplanInterface $buildplan, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface => new ModuleRumpWrapperWarpcore($wrapper, $buildplan->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_WARPCORE)),
            ShipModuleTypeEnum::MODULE_TYPE_FUSIONREACTOR => fn (ShipBuildplanInterface $buildplan, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface => new ModuleRumpWrapperReactor($wrapper, $buildplan->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_FUSIONREACTOR)),
            ShipModuleTypeEnum::MODULE_TYPE_COMPUTER => fn (ShipBuildplanInterface $buildplan, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface => new ModuleRumpWrapperComputer($wrapper, $buildplan->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_COMPUTER)),
            ShipModuleTypeEnum::MODULE_TYPE_PHASER => fn (ShipBuildplanInterface $buildplan, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface => new ModuleRumpWrapperEnergyWeapon($wrapper, $buildplan->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_PHASER)),
            ShipModuleTypeEnum::MODULE_TYPE_TORPEDO => fn (ShipBuildplanInterface $buildplan, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface => new ModuleRumpWrapperProjectileWeapon($wrapper, $buildplan->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_TORPEDO)),
            ShipModuleTypeEnum::MODULE_TYPE_SENSOR => fn (ShipBuildplanInterface $buildplan, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface => new ModuleRumpWrapperSensor($wrapper, $buildplan->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_SENSOR)),
            ShipModuleTypeEnum::MODULE_TYPE_WARPDRIVE => fn (ShipBuildplanInterface $buildplan, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface => new ModuleRumpWrapperWarpDrive($wrapper, $buildplan->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_WARPDRIVE)),
            ShipModuleTypeEnum::MODULE_TYPE_SPECIAL => function (ShipBuildplanInterface $buildplan, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface {
                $specialMods = $buildplan->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_SPECIAL);
                return new ModuleRumpWrapperSpecial($wrapper, $specialMods);
            },
        ];

        $wrapper = $this->shipWrapperFactory->wrapShip($ship);

        foreach ($moduleTypeList as $moduleTypeId => $wrapperCallable) {
            if ($this->loggerUtil->doLog()) {
                $this->loggerUtil->log(sprintf("moduleTypeId: %d", $moduleTypeId));
            }
            $buildplanModules = $buildplan->getModulesByType($moduleTypeId);
            if ($buildplanModules !== []) {
                if ($this->loggerUtil->doLog()) {
                    $this->loggerUtil->log("wrapperCallable!");
                }
                $moduleRumpWrapper = $wrapperCallable($buildplan, $wrapper);
                $moduleRumpWrapper->apply($ship);
            }
        }

        if ($ship->getName() == '' || $ship->getName() === sprintf('%s in Bau', $ship->getRump()->getName())) {
            $ship->setName($ship->getRump()->getName());
        }
        $ship->setSensorRange($ship->getRump()->getBaseSensorRange());

        $ship->setAlertStateGreen();

        $this->shipRepository->save($ship);
        if ($colony !== null) {
            $starsystemMap = $this->starSystemMapRepository->getByCoordinates($colony->getSystem()->getId(), $colony->getSx(), $colony->getSy());

            $ship->setCx($colony->getSystem()->getCx());
            $ship->setCy($colony->getSystem()->getCy());
            $ship->setStarsystemMap($starsystemMap);
            $this->shipRepository->save($ship);
        }

        return $wrapper;
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
            $systems[ShipSystemTypeEnum::SYSTEM_DEFLECTOR] = 0;
            $systems[ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM] = 0;
        }
        $systems[ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT] = 0;
        //TODO transporter

        if ($ship->getRump()->getCategoryId() === ShipRumpEnum::SHIP_CATEGORY_STATION) {
            $systems[ShipSystemTypeEnum::SYSTEM_BEAM_BLOCKER] = 0;
        }

        if ($ship->getRump()->isShipyard()) {
            $systems[ShipSystemTypeEnum::SYSTEM_CONSTRUCTION_HUB] = 0;
        }

        if ($ship->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_SENSOR) {
            $systems[ShipSystemTypeEnum::SYSTEM_UPLINK] = 0;
        }

        foreach ($modules as $module) {
            switch ($module->getModule()->getType()) {
                case ShipModuleTypeEnum::MODULE_TYPE_SHIELDS:
                    $systems[ShipSystemTypeEnum::SYSTEM_SHIELDS] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_WARPDRIVE:
                    $systems[ShipSystemTypeEnum::SYSTEM_WARPDRIVE] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_EPS:
                    $systems[ShipSystemTypeEnum::SYSTEM_EPS] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_IMPULSEDRIVE:
                    $systems[ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_REACTOR:
                    if ($module->getModule()->getId() === ShipModuleTypeEnum::MODULE_ID_FUSIONREACTOR) {
                        $systems[ShipSystemTypeEnum::SYSTEM_FUSION_REACTOR] = $module->getModule();
                        break;
                    } else {
                        $systems[ShipSystemTypeEnum::SYSTEM_WARPCORE] = $module->getModule();
                        break;
                    }
                case ShipModuleTypeEnum::MODULE_TYPE_COMPUTER:
                    $systems[ShipSystemTypeEnum::SYSTEM_COMPUTER] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_PHASER:
                    $systems[ShipSystemTypeEnum::SYSTEM_PHASER] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_TORPEDO:
                    $systems[ShipSystemTypeEnum::SYSTEM_TORPEDO] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_SENSOR:
                    $systems[ShipSystemTypeEnum::SYSTEM_LSS] = $module->getModule();
                    $systems[ShipSystemTypeEnum::SYSTEM_NBS] = 0;
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_SPECIAL:
                    $this->addSpecialSystems($module->getModule(), $systems);
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
            $obj->setSystemType($systemType);
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
                    $systems[ShipSystemTypeEnum::SYSTEM_CLOAK] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_TACHYON_SCANNER:
                    $systems[ShipSystemTypeEnum::SYSTEM_TACHYON_SCANNER] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_TROOP_QUARTERS:
                    $systems[ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_ASTRO_LABORATORY:
                    $systems[ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_SUBSPACE_FIELD_SENSOR:
                    $systems[ShipSystemTypeEnum::SYSTEM_SUBSPACE_SCANNER] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_MATRIX_SENSOR:
                    $systems[ShipSystemTypeEnum::SYSTEM_MATRIX_SCANNER] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_TORPEDO_STORAGE:
                    $systems[ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_SHUTTLE_RAMP:
                    $systems[ShipSystemTypeEnum::SYSTEM_SHUTTLE_RAMP] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_TRANSWARP_COIL:
                    $systems[ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_HIROGEN_TRACKER:
                    $systems[ShipSystemTypeEnum::SYSTEM_TRACKER] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_THOLIAN_WEB:
                    $systems[ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_RPG:
                    $systems[ShipSystemTypeEnum::SYSTEM_RPG_MODULE] = 0;
                    break;
            }
        }
    }
}
