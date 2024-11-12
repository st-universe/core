<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShip;

use Override;
use request;
use Stu\Component\Game\GameEnum;
use Stu\Component\Player\ColonizationCheckerInterface;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Component\Ship\Nbs\NbsUtilityInterface;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Lib\SessionInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Lib\Map\NavPanel\NavPanel;
use Stu\Module\Control\ViewContext;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Control\ViewWithTutorialInterface;
use Stu\Module\Database\View\Category\Wrapper\DatabaseCategoryWrapperFactoryInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\Ui\ShipUiFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StationShipRepairInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\ShipyardShipQueueRepositoryInterface;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;
use Stu\Orm\Repository\UserLayerRepositoryInterface;

final class ShowShip implements ViewControllerInterface, ViewWithTutorialInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIP';

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private DatabaseUserRepositoryInterface $databaseUserRepository,
        private StationShipRepairRepositoryInterface $stationShipRepairRepository,
        private ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository,
        private UserLayerRepositoryInterface $userLayerRepository,
        private AnomalyRepositoryInterface $anomalyRepository,
        private DatabaseCategoryWrapperFactoryInterface $databaseCategoryWrapperFactory,
        private NbsUtilityInterface $nbsUtility,
        private StationUtilityInterface $stationUtility,
        private ShipWrapperFactoryInterface $shipWrapperFactory,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private ShipUiFactoryInterface $shipUiFactory,
        private ShipCrewCalculatorInterface $shipCrewCalculator,
        private AstroEntryLibInterface $astroEntryLib,
        private ColonizationCheckerInterface $colonizationChecker,
        private SessionInterface $session,
        private LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $this->loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $ownsCurrentColony = false;

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            true,
            false
        );
        $ship = $wrapper->get();

        $tachyonFresh = $game->getViewContext(ViewContextTypeEnum::TACHYON_SCAN_JUST_HAPPENED) ?? false;
        $tachyonActive = $tachyonFresh;

        // check if tachyon scan still active
        if (!$tachyonActive) {
            $tachyonActive = $this->nbsUtility->isTachyonActive($ship);
        }

        $rump = $ship->getRump();

        $colony = $this->getColony($ship);
        $canColonize = false;
        if ($colony !== null) {
            if ($rump->hasSpecialAbility(ShipRumpSpecialAbilityEnum::COLONIZE)) {
                $canColonize = $this->colonizationChecker->canColonize($user, $colony);
            }
            $ownsCurrentColony = $colony->getUser() === $user;
        }

        //Forschungseintrag erstellen, damit System-Link optional erstellt werden kann
        $starSystem = $ship->getSystem() ?? $ship->isOverSystem();
        if ($starSystem !== null && $starSystem->getDatabaseEntry() !== null) {
            $starSystemEntryTal = $this->databaseCategoryWrapperFactory->createDatabaseCategoryEntryWrapper($starSystem->getDatabaseEntry(), $user);
            $game->setTemplateVar('STARSYSTEM_ENTRY_TAL', $starSystemEntryTal);
        }

        $isBase = $ship->isBase();
        $game->appendNavigationPart(
            $isBase ? 'station.php' : 'ship.php',
            $isBase ? _('Stationen') : _('Schiffe')
        );

        $game->appendNavigationPart(
            sprintf('?%s=1&id=%d', static::VIEW_IDENTIFIER, $ship->getId()),
            $ship->getName()
        );

        $game->setViewTemplate('html/ship/ship.twig');
        $game->setTemplateVar('WRAPPER', $wrapper);

        if ($ship->getLss()) {

            $this->createUserLayerIfNecessary($user, $ship);

            $game->setTemplateVar('VISUAL_NAV_PANEL', $this->shipUiFactory->createVisualNavPanel(
                $ship,
                $game->getUser(),
                $this->loggerUtilFactory->getLoggerUtil(),
                $tachyonFresh
            ));
        }
        $game->setTemplateVar('NAV_PANEL', new NavPanel($ship));

        $this->doConstructionStuff($ship, $game);
        $this->doStationStuff($ship, $game);

        $this->nbsUtility->setNbsTemplateVars($ship, $game, $this->session, $tachyonActive);

        $game->setTemplateVar('ASTRO_STATE_SYSTEM', $this->getAstroState($ship, $game, true));
        $game->setTemplateVar('ASTRO_STATE_REGION', $this->getAstroState($ship, $game, false));
        $game->setTemplateVar('TACHYON_ACTIVE', $tachyonActive);
        $game->setTemplateVar('CAN_COLONIZE', $canColonize);
        $game->setTemplateVar('OWNS_CURRENT_COLONY', $ownsCurrentColony);
        $game->setTemplateVar('CURRENT_COLONY', $colony);
        $game->setTemplateVar('CLOSEST_ANOMALY_DISTANCE', $this->anomalyRepository->getClosestAnomalyDistance($ship));

        $userLayers = $user->getUserLayers();
        if ($ship->hasTranswarp()) {
            $game->setTemplateVar('USER_LAYERS', $userLayers);
        }

        $layer = $ship->getLayer();
        if ($layer !== null && $userLayers->containsKey($layer->getId())) {
            $game->setTemplateVar('IS_MAP_BUTTON_VISIBLE', true);
        }

        $crewObj = $this->shipCrewCalculator->getCrewObj($rump);

        $game->setTemplateVar(
            'MAX_CREW_COUNT',
            $crewObj === null
                ? null
                : $this->shipCrewCalculator->getMaxCrewCountByShip($ship)
        );

        $game->addExecuteJS(sprintf("setShipIdAndSstr(%d, '%s')", $ship->getId(), $game->getSessionString()));
        $this->addWarpcoreSplitJavascript($wrapper, $game);

        $this->loggerUtil->log(sprintf('ShowShip.handle-end, timestamp: %F', microtime(true)));
    }

    private function createUserLayerIfNecessary(UserInterface $user, ShipInterface $ship): void
    {
        $layer = $ship->getLayer();
        if ($layer === null) {
            return;
        }

        if ($ship->getMap() === null) {
            return;
        }

        $hasSeenLayer = $user->hasSeen($layer->getId());
        if ($hasSeenLayer) {
            return;
        }

        $userLayer = $this->userLayerRepository->prototype();
        $userLayer->setLayer($layer);
        $userLayer->setUser($user);
        $this->userLayerRepository->save($userLayer);

        $user->getUserLayers()->set($layer->getId(), $userLayer);
    }

    private function addWarpcoreSplitJavascript(ShipWrapperInterface $wrapper, GameControllerInterface $game): void
    {
        $reactor = $wrapper->getReactorWrapper();
        $warpDriveSystem = $wrapper->getWarpDriveSystemData();
        $epsSystem = $wrapper->getEpsSystemData();

        if (
            $warpDriveSystem !== null
            && $epsSystem !== null
            && $reactor !== null
        ) {
            $ship = $wrapper->get();

            $game->addExecuteJS(sprintf(
                'setReactorSplitConstants(%d,%d,%d,%d,%d,%d);',
                $reactor->getOutputCappedByLoad(),
                $wrapper->getEpsUsage(),
                $ship->getRump()->getFlightEcost(),
                $epsSystem->getMaxEps() - $epsSystem->getEps(),
                $warpDriveSystem->getWarpDrive(),
                $warpDriveSystem->getMaxWarpDrive()
            ), GameEnum::JS_EXECUTION_AFTER_RENDER);
            $game->addExecuteJS(sprintf(
                'updateReactorValues(%d);',
                $warpDriveSystem->getWarpDriveSplit(),
            ), GameEnum::JS_EXECUTION_AFTER_RENDER);
        }
    }

    private function getColony(ShipInterface $ship): ?ColonyInterface
    {
        if ($ship->getStarsystemMap() === null) {
            return null;
        }

        return $ship->getStarsystemMap()->getColony();
    }

    private function getAstroState(ShipInterface $ship, GameControllerInterface $game, bool $isSystem): AstroStateWrapper
    {
        //$this->loggerUtil->init('SS', LoggerEnum::LEVEL_ERROR);

        $databaseEntry = $this->getDatabaseEntryForShipLocation($ship, $isSystem);

        $this->loggerUtil->log(sprintf('databaseEntry: %d', $databaseEntry !== null ? $databaseEntry->getId() : 0));

        $astroEntry = null;

        if ($databaseEntry === null) {
            $state = AstronomicalMappingEnum::NONE;
        } elseif ($this->databaseUserRepository->exists($game->getUser()->getId(), $databaseEntry->getId())) {
            $state = AstronomicalMappingEnum::DONE;
        } else {
            $astroEntry = $this->astroEntryLib->getAstroEntryByShipLocation($ship, $isSystem);

            $this->loggerUtil->log(sprintf('isSystem: %b, astroEntry?: %b', $isSystem, $astroEntry !== null));

            $state = $astroEntry === null ? AstronomicalMappingEnum::PLANNABLE : $astroEntry->getState();
        }
        $turnsLeft = null;
        if ($state === AstronomicalMappingEnum::FINISHING && $astroEntry !== null) {
            $turnsLeft = AstronomicalMappingEnum::TURNS_TO_FINISH - ($game->getCurrentRound()->getTurn() - $astroEntry->getAstroStartTurn());
        }

        $wrapper = new AstroStateWrapper($state, $turnsLeft, $isSystem);

        $this->loggerUtil->log(sprintf('type: %s', $wrapper->getType()));

        return $wrapper;
    }

    private function getDatabaseEntryForShipLocation(ShipInterface $ship, bool $isSystem): ?DatabaseEntryInterface
    {
        if ($isSystem) {
            $system = $ship->getSystem() ?? $ship->isOverSystem();
            if ($system !== null) {
                return $system->getDatabaseEntry();
            }

            return null;
        }

        $mapRegion = $ship->getMapRegion();
        if ($mapRegion !== null) {
            $this->loggerUtil->log('mapREgion found');
            return $mapRegion->getDatabaseEntry();
        }

        return null;
    }

    private function doConstructionStuff(ShipInterface $ship, GameControllerInterface $game): void
    {
        if (!$ship->isConstruction() && !$ship->isBase()) {
            return;
        }

        $progress =  $this->stationUtility->getConstructionProgress($ship);
        if ($progress === null || $progress->getRemainingTicks() === 0) {
            $game->setTemplateVar('CONSTRUCTION_PROGRESS_WRAPPER', null);
        } else {
            $dockedWorkbees = $this->stationUtility->getDockedWorkbeeCount($ship);
            $neededWorkbees = $this->stationUtility->getNeededWorkbeeCount($ship, $ship->getRump());

            $game->setTemplateVar('CONSTRUCTION_PROGRESS_WRAPPER', new ConstructionProgressWrapper(
                $progress,
                $ship,
                $dockedWorkbees,
                $neededWorkbees
            ));
        }

        if ($progress === null || $progress->getRemainingTicks() == 0) {
            $plans = $this->stationUtility->getStationBuildplansByUser($game->getUser()->getId());
            $game->setTemplateVar('POSSIBLE_STATIONS', $plans);

            $moduleSelectors = [];
            foreach ($plans as $plan) {
                $ms = $this->colonyLibFactory->createModuleSelector(
                    ShipModuleTypeEnum::SPECIAL,
                    $ship,
                    $plan->getRump(),
                    $game->getUser()
                );

                $moduleSelectors[] = $ms;
            }

            $game->setTemplateVar('MODULE_SELECTORS', $moduleSelectors);
        }
    }

    private function doStationStuff(ShipInterface $ship, GameControllerInterface $game): void
    {
        if ($this->stationUtility->canManageShips($ship)) {
            $game->setTemplateVar('CAN_MANAGE', true);
        }

        if ($this->stationUtility->canRepairShips($ship)) {
            $game->setTemplateVar('CAN_REPAIR', true);

            $shipRepairProgress = array_map(
                fn(StationShipRepairInterface $repair): ShipWrapperInterface => $this->shipWrapperFactory->wrapShip($repair->getShip()),
                $this->stationShipRepairRepository->getByStation(
                    $ship->getId()
                )
            );

            $game->setTemplateVar('SHIP_REPAIR_PROGRESS', $shipRepairProgress);
        }

        if ($ship->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_SHIPYARD) {
            $game->setTemplateVar('SHIP_BUILD_PROGRESS', $this->shipyardShipQueueRepository->getByShipyard($ship->getId()));
        }

        $firstOrbitShip = null;

        $dockedShips = $ship->getDockedShips();
        if (!$dockedShips->isEmpty()) {
            // if selected, return the current target
            $target = request::postInt('target');

            if ($target !== 0) {
                foreach ($dockedShips as $ship) {
                    if ($ship->getId() === $target) {
                        $firstOrbitShip = $ship;
                    }
                }
            }
            if ($firstOrbitShip === null) {
                $firstOrbitShip = $dockedShips->first();
            }
        }

        $game->setTemplateVar('FIRST_MANAGE_SHIP', $firstOrbitShip !== null ? $this->shipWrapperFactory->wrapShip($firstOrbitShip) : null);
        $game->setTemplateVar('CAN_UNDOCK', true);

        if ($ship->getRump()->isShipyard()) {
            $game->setTemplateVar('AVAILABLE_BUILDPLANS', $this->stationUtility->getShipyardBuildplansByUser($game->getUser()->getId()));
        }
    }
    public function getViewContext(): ViewContext
    {
        return new ViewContext(ModuleViewEnum::SHIP, self::VIEW_IDENTIFIER);
    }
}
