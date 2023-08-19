<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShip;

use NavPanel;
use request;
use Stu\Component\Player\ColonizationCheckerInterface;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Component\Ship\Nbs\NbsUtilityInterface;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Lib\ColonyStorageCommodityWrapper\ColonyStorageCommodityWrapper;
use Stu\Lib\SessionInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\View\Category\Tal\DatabaseCategoryTalFactoryInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\Ui\ShipUiFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StationShipRepairInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\ShipyardShipQueueRepositoryInterface;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;

final class ShowShip implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP';

    private SessionInterface $session;

    private LoggerUtilFactoryInterface $loggerUtilFactory;

    private LoggerUtilInterface $loggerUtil;

    private ShipLoaderInterface $shipLoader;

    private ColonizationCheckerInterface $colonizationChecker;

    private DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory;

    private AstroEntryRepositoryInterface $astroEntryRepository;

    private DatabaseUserRepositoryInterface $databaseUserRepository;

    private NbsUtilityInterface $nbsUtility;

    private StationShipRepairRepositoryInterface $stationShipRepairRepository;

    private ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository;

    private StationUtilityInterface $stationUtility;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ShipUiFactoryInterface $shipUiFactory;

    private ShipCrewCalculatorInterface $shipCrewCalculator;

    private FightLibInterface $fightLib;

    public function __construct(
        SessionInterface $session,
        ShipLoaderInterface $shipLoader,
        ColonizationCheckerInterface $colonizationChecker,
        DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory,
        AstroEntryRepositoryInterface $astroEntryRepository,
        DatabaseUserRepositoryInterface $databaseUserRepository,
        NbsUtilityInterface $nbsUtility,
        StationShipRepairRepositoryInterface $stationShipRepairRepository,
        ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository,
        StationUtilityInterface $stationUtility,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        ColonyLibFactoryInterface $colonyLibFactory,
        ShipUiFactoryInterface $shipUiFactory,
        ShipCrewCalculatorInterface $shipCrewCalculator,
        FightLibInterface $fightLib,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->session = $session;
        $this->shipLoader = $shipLoader;
        $this->colonizationChecker = $colonizationChecker;
        $this->databaseCategoryTalFactory = $databaseCategoryTalFactory;
        $this->astroEntryRepository = $astroEntryRepository;
        $this->databaseUserRepository = $databaseUserRepository;
        $this->nbsUtility = $nbsUtility;
        $this->stationShipRepairRepository = $stationShipRepairRepository;
        $this->shipyardShipQueueRepository = $shipyardShipQueueRepository;
        $this->stationUtility = $stationUtility;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->loggerUtilFactory = $loggerUtilFactory;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        $this->colonyLibFactory = $colonyLibFactory;
        $this->shipUiFactory = $shipUiFactory;
        $this->shipCrewCalculator = $shipCrewCalculator;
        $this->fightLib = $fightLib;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $ownsCurrentColony = false;

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            true
        );
        $ship = $wrapper->get();

        $tachyonFresh = $game->getViewContext()['TACHYON_SCAN_JUST_HAPPENED'] ?? false;
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
        $starSystem = $ship->getSystem();
        if ($starSystem !== null && $starSystem->getDatabaseEntry() !== null) {
            $starSystemEntryTal = $this->databaseCategoryTalFactory->createDatabaseCategoryEntryTal($starSystem->getDatabaseEntry(), $user);
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
        $game->setPagetitle($ship->getName());

        $game->setTemplateFile('html/ship.twig', true);

        $game->setTemplateVar('WRAPPER', $wrapper);

        if ($ship->getLss()) {
            $game->setTemplateVar('VISUAL_NAV_PANEL', $this->shipUiFactory->createVisualNavPanel(
                $ship,
                $game->getUser(),
                $this->loggerUtilFactory->getLoggerUtil(),
                $ship->getTachyonState(),
                $tachyonFresh
            ));
        }
        $game->setTemplateVar('NAV_PANEL', new NavPanel($ship));

        $this->doConstructionStuff($ship, $game);
        $this->doStationStuff($ship, $game);

        $this->nbsUtility->setNbsTemplateVars($ship, $game, $this->session, $tachyonActive);

        $game->setTemplateVar('ASTRO_STATE', $this->getAstroState($ship, $game));
        $game->setTemplateVar('TACHYON_ACTIVE', $tachyonActive);
        $game->setTemplateVar('CAN_COLONIZE', $canColonize);
        $game->setTemplateVar('OWNS_CURRENT_COLONY', $ownsCurrentColony);
        $game->setTemplateVar('CURRENT_COLONY', $colony);
        $game->setTemplateVar('FIGHT_LIB', $this->fightLib);
        if ($ship->hasTranswarp()) {
            $game->setTemplateVar('USER_LAYERS', $user->getUserLayers());
        }

        $crewObj = $this->shipCrewCalculator->getCrewObj($rump);

        $game->setTemplateVar(
            'MAX_CREW_COUNT',
            $crewObj === null
                ? null
                : $this->shipCrewCalculator->getMaxCrewCountByShip($ship)
        );

        $this->addWarpcoreSplitJavascript($wrapper, $game);

        $this->loggerUtil->log(sprintf('ShowShip.handle-end, timestamp: %F', microtime(true)));
    }

    private function addWarpcoreSplitJavascript(ShipWrapperInterface $wrapper, GameControllerInterface $game): void
    {
        $warpCoreSystem = $wrapper->getWarpCoreSystemData();
        $warpDriveSystem = $wrapper->getWarpDriveSystemData();
        $epsSystem = $wrapper->getEpsSystemData();

        if ($warpCoreSystem !== null && $warpDriveSystem !== null && $epsSystem !== null) {
            $ship = $wrapper->get();

            $game->addExecuteJS(sprintf(
                'updateEPSSplitValue(%d,%d,%d,%d,%d,%d,%d,%d,%d,%d);',
                $warpCoreSystem->getWarpCoreSplit(),
                $ship->getReactorOutput(),
                $wrapper->getEpsUsage(),
                $ship->getRump()->getFlightEcost(),
                $wrapper->getEffectiveEpsProduction(),
                $epsSystem->getEps(),
                $epsSystem->getMaxEps(),
                $warpDriveSystem->getWarpDrive(),
                $warpDriveSystem->getMaxWarpDrive(),
                $ship->getReactorLoad()
            ), true);
        }
    }

    private function getColony(ShipInterface $ship): ?ColonyInterface
    {
        if ($ship->getStarsystemMap() === null) {
            return null;
        }

        return $ship->getStarsystemMap()->getColony();
    }

    private function getAstroState(ShipInterface $ship, GameControllerInterface $game): AstroStateWrapper
    {
        $system = $ship->getSystem() ?? $ship->isOverSystem();

        $astroEntry = null;
        if ($system === null || $system->getDatabaseEntry() === null) {
            $state = AstronomicalMappingEnum::NONE;
        } elseif ($this->databaseUserRepository->exists($game->getUser()->getId(), $system->getDatabaseEntry()->getId())) {
            $state = AstronomicalMappingEnum::DONE;
        } else {
            $astroEntry = $this->astroEntryRepository->getByUserAndSystem(
                $ship->getUser()->getId(),
                $system->getId()
            );

            $state = $astroEntry === null ? AstronomicalMappingEnum::PLANNABLE : $astroEntry->getState();
        }
        $turnsLeft = null;
        if ($state === AstronomicalMappingEnum::FINISHING && $astroEntry !== null) {
            $turnsLeft = AstronomicalMappingEnum::TURNS_TO_FINISH - ($game->getCurrentRound()->getTurn() - $astroEntry->getAstroStartTurn());
        }
        return new AstroStateWrapper($state, $turnsLeft);
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

        if ($progress === null) {
            $plans = $this->stationUtility->getStationBuildplansByUser($game->getUser()->getId());
            $game->setTemplateVar('POSSIBLE_STATIONS', $plans);

            $moduleSelectors = [];
            foreach ($plans as $plan) {
                $ms = $this->colonyLibFactory->createModuleSelectorSpecial(
                    ShipModuleTypeEnum::MODULE_TYPE_SPECIAL,
                    null,
                    $ship,
                    $plan->getRump(),
                    $game->getUser()->getId()
                );

                $ms->setDummyId($plan->getId());
                $moduleSelectors[] = $ms;
            }

            $game->setTemplateVar('MODULE_SELECTORS', $moduleSelectors);
            $game->setTemplateVar('HAS_STORAGE', new ColonyStorageCommodityWrapper($ship->getStorage()));
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
                fn (StationShipRepairInterface $repair): ShipWrapperInterface => $this->shipWrapperFactory->wrapShip($repair->getShip()),
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

        /**
         * @var ShipInterface[] $shipList
         */
        $shipList = $ship->getDockedShips()->toArray();
        if ($shipList !== []) {
            // if selected, return the current target
            $target = request::postInt('target');

            if ($target !== 0) {
                foreach ($shipList as $ship) {
                    if ($ship->getId() === $target) {
                        $firstOrbitShip = $ship;
                    }
                }
            }
            if ($firstOrbitShip === null) {
                $firstOrbitShip = current($shipList);
            }
        }

        $game->setTemplateVar('FIRST_MANAGE_SHIP', $firstOrbitShip !== null ? $this->shipWrapperFactory->wrapShip($firstOrbitShip) : null);
        $game->setTemplateVar('CAN_UNDOCK', true);

        if ($ship->getRump()->isShipyard()) {
            $game->setTemplateVar('AVAILABLE_BUILDPLANS', $this->stationUtility->getShipyardBuildplansByUser($game->getUser()->getId()));
        }
    }
}
