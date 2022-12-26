<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShip;

use NavPanel;
use request;
use Stu\Component\Player\ColonizationCheckerInterface;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\Nbs\NbsUtilityInterface;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Lib\ColonyStorageCommodityWrapper\ColonyStorageCommodityWrapper;
use Stu\Lib\ModuleScreen\ModuleSelectorSpecial;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\View\Category\Tal\DatabaseCategoryTalFactoryInterface;
use Stu\Lib\SessionInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StationShipRepairInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\ShipyardShipQueueRepositoryInterface;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;
use VisualNavPanel;

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
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $ownsCurrentColony = false;

        // $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            true
        );

        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tmark1, seconds: %F", $endTime - $startTime));
        }

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        $tachyonFresh = $game->getViewContext()['TACHYON_SCAN_JUST_HAPPENED'] ?? false;
        $tachyonActive = $tachyonFresh;

        // check if tachyon scan still active
        if (!$tachyonActive) {
            $tachyonActive = $this->nbsUtility->isTachyonActive($ship);
        }

        $colony = $this->getColony($ship);
        $canColonize = false;
        if ($colony) {
            if ($ship->getRump()->hasSpecialAbility(ShipRumpSpecialAbilityEnum::COLONIZE)) {
                $canColonize = $this->colonizationChecker->canColonize($user, $colony);
            }
            $ownsCurrentColony = $colony->getUser() === $user;
        }

        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tmark2, seconds: %F", $endTime - $startTime));
        }

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }

        //Forschungseintrag erstellen, damit System-Link optional erstellt werden kann
        $starsystem = null;
        if ($ship->getSystem() !== null) {
            $starsystem = $this->databaseCategoryTalFactory->createDatabaseCategoryEntryTal($ship->getSystem()->getDatabaseEntry(), $user);
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
        $game->setTemplateFile('html/ship.xhtml');

        $game->setTemplateVar('WRAPPER', $this->shipWrapperFactory->wrapShip($ship));

        if ($ship->isFleetLeader()) {
            $game->setTemplateVar('FLEETWRAPPER', $this->shipWrapperFactory->wrapFleet($ship->getFleet()));
        }
        if ($starsystem !== null) {
            $game->setTemplateVar('STARSYSTEM_ENTRY_TAL', $starsystem);
        }
        if ($ship->getLss()) {
            $game->setTemplateVar('VISUAL_NAV_PANEL', new VisualNavPanel(
                $ship,
                $game->getUser(),
                $this->loggerUtilFactory->getLoggerUtil(),
                $ship->getTachyonState(),
                $tachyonFresh
            ));
        }
        $game->setTemplateVar('NAV_PANEL', new NavPanel($ship));

        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tmark3, seconds: %F", $endTime - $startTime));
        }

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }

        $this->doConstructionStuff($ship, $game);
        $this->doStationStuff($ship, $game);

        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tmark4, seconds: %F", $endTime - $startTime));
        }

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }

        $this->nbsUtility->setNbsTemplateVars($ship, $game, $this->session, $tachyonActive);

        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tmark5, seconds: %F", $endTime - $startTime));
        }

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }

        $game->setTemplateVar('ASTRO_STATE', $this->getAstroState($ship, $game));

        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tmark6, seconds: %F", $endTime - $startTime));
        }
        $game->setTemplateVar('TACHYON_ACTIVE', $tachyonActive);
        $game->setTemplateVar('CAN_COLONIZE_CURRENT_COLONY', $canColonize);
        $game->setTemplateVar('OWNS_CURRENT_COLONY', $ownsCurrentColony);
        $game->setTemplateVar('CURRENT_COLONY', $colony);

        $this->loggerUtil->log(sprintf('ShowShip.php-end, timestamp: %F', microtime(true)));
    }

    private function getColony(ShipInterface $ship): ?ColonyInterface
    {
        if ($ship->getStarsystemMap() === null) {
            return null;
        }

        return $ship->getStarsystemMap()->getColony();
    }

    private function getAstroState(ShipInterface $ship, GameControllerInterface $game)
    {
        $system = $ship->getSystem() !== null ? $ship->getSystem() : $ship->isOverSystem();

        if ($system === null) {
            $state = AstronomicalMappingEnum::NONE;
        } else {
            if ($this->databaseUserRepository->exists($game->getUser()->getId(), $system->getDatabaseEntry()->getId())) {
                $state = AstronomicalMappingEnum::DONE;
            } else {
                $astroEntry = $this->astroEntryRepository->getByUserAndSystem(
                    $ship->getUser()->getId(),
                    $system->getId()
                );

                if ($astroEntry === null) {
                    $state = AstronomicalMappingEnum::PLANNABLE;
                } else {
                    $state = $astroEntry->getState();
                }
            }
        }
        if ($state === AstronomicalMappingEnum::FINISHING) {
            $turnsLeft = AstronomicalMappingEnum::TURNS_TO_FINISH - ($game->getCurrentRound()->getTurn() - $astroEntry->getAstroStartTurn());
            $game->setTemplateVar('ASTRO_LEFT', $turnsLeft);
        }
        return new AstroStateWrapper($state);
    }

    private function doConstructionStuff(ShipInterface $ship, GameControllerInterface $game): void
    {
        if (!$ship->isConstruction() && !$ship->isBase()) {
            return;
        }

        $progress =  $this->stationUtility->getConstructionProgress($ship);
        if ($progress !== null && $progress->getRemainingTicks() === 0) {
            $progress = null;
        } else {
            $dockedWorkbees = $this->stationUtility->getDockedWorkbeeCount($ship);
            $neededWorkbees = $ship->getState() === ShipStateEnum::SHIP_STATE_UNDER_CONSTRUCTION
                ? $ship->getRump()->getNeededWorkbees() :
                (int)ceil($ship->getRump()->getNeededWorkbees() / 2);

            $game->setTemplateVar('DOCKED', $dockedWorkbees);
            if ($ship->getState() === ShipStateEnum::SHIP_STATE_UNDER_SCRAPPING) {
                $game->setTemplateVar('NEEDED', $neededWorkbees);
            } else {
                $game->setTemplateVar('NEEDED', $neededWorkbees);
            }
            $game->setTemplateVar('WORKBEECOLOR', $dockedWorkbees < $neededWorkbees ? 'red' : 'green');
        }
        $game->setTemplateVar('PROGRESS', $progress);
        $game->setTemplateVar('SHIP_STATE_UNDER_CONSTRUCTION', ShipStateEnum::SHIP_STATE_UNDER_CONSTRUCTION);
        $game->setTemplateVar('SHIP_STATE_UNDER_SCRAPPING', ShipStateEnum::SHIP_STATE_UNDER_SCRAPPING);

        if ($progress === null) {
            $plans = $this->stationUtility->getStationBuildplansByUser($game->getUser()->getId());
            $game->setTemplateVar('POSSIBLE_STATIONS', $plans);

            $moduleSelectors = [];
            foreach ($plans as $plan) {

                $ms = new ModuleSelectorSpecial(
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
                function (StationShipRepairInterface $repair): ShipWrapperInterface {
                    return $this->shipWrapperFactory->wrapShip($repair->getShip());
                },
                $this->stationShipRepairRepository->getByStation(
                    $ship->getId()
                )
            );

            $game->setTemplateVar('SHIP_REPAIR_PROGRESS', $shipRepairProgress);
        }

        if ($ship->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_SHIPYARD) {
            $game->setTemplateVar('SHIP_BUILD_PROGRESS', $this->shipyardShipQueueRepository->getByShipyard($ship->getId()));
        }

        /**
         * @var ShipInterface[] $shipList
         */
        $shipList = $ship->getDockedShips()->toArray();
        if (!empty($shipList)) {
            // if selected, return the current target
            $target = request::postInt('target');

            if ($target) {
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

        $game->setTemplateVar('FIRST_MANAGE_SHIP', $firstOrbitShip ? $this->shipWrapperFactory->wrapShip($firstOrbitShip) : null);
        $game->setTemplateVar('CAN_UNDOCK', true);

        if ($ship->getRump()->isShipyard()) {
            $game->setTemplateVar('AVAILABLE_BUILDPLANS', $this->stationUtility->getShipyardBuildplansByUser($game->getUser()->getId()));
        }
    }
}
