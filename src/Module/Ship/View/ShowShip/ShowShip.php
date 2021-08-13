<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShip;

use NavPanel;
use request;
use Stu\Component\Player\ColonizationCheckerInterface;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\Nbs\NbsUtilityInterface;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Exception\ShipDoesNotExistException;
use Stu\Lib\ColonyStorageGoodWrapper\ColonyStorageGoodWrapper;
use Stu\Lib\ModuleScreen\ModuleSelectorSpecial;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\View\Category\Tal\DatabaseCategoryTalFactoryInterface;
use Stu\Lib\SessionInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use VisualNavPanel;

final class ShowShip implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP';

    private SessionInterface $session;

    private LoggerUtilInterface $loggerUtil;

    private ShipLoaderInterface $shipLoader;

    private ColonyRepositoryInterface $colonyRepository;

    private ColonizationCheckerInterface $colonizationChecker;

    private DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory;

    private AstroEntryRepositoryInterface $astroEntryRepository;

    private DatabaseUserRepositoryInterface $databaseUserRepository;

    private NbsUtilityInterface $nbsUtility;

    private StationUtilityInterface $stationUtility;

    public function __construct(
        SessionInterface $session,
        LoggerUtilInterface $loggerUtil,
        ShipLoaderInterface $shipLoader,
        ColonyRepositoryInterface $colonyRepository,
        ColonizationCheckerInterface $colonizationChecker,
        DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory,
        AstroEntryRepositoryInterface $astroEntryRepository,
        DatabaseUserRepositoryInterface $databaseUserRepository,
        NbsUtilityInterface $nbsUtility,
        StationUtilityInterface $stationUtility
    ) {
        $this->session = $session;
        $this->loggerUtil = $loggerUtil;
        $this->shipLoader = $shipLoader;
        $this->colonyRepository = $colonyRepository;
        $this->colonizationChecker = $colonizationChecker;
        $this->databaseCategoryTalFactory = $databaseCategoryTalFactory;
        $this->astroEntryRepository = $astroEntryRepository;
        $this->databaseUserRepository = $databaseUserRepository;
        $this->nbsUtility = $nbsUtility;
        $this->stationUtility = $stationUtility;
    }

    public function handle(GameControllerInterface $game): void
    {
        if ($game->getUser()->getId() === 126) {
            $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);
        } else {
            $this->loggerUtil->init();
        }

        $user = $game->getUser();
        $userId = $user->getId();
        $ownsCurrentColony = false;

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }

        try {
            $ship = $this->shipLoader->getByIdAndUser(
                request::indInt('id'),
                $userId
            );
        } catch (ShipDoesNotExistException $e) {
            $game->addInformation(_('Dieses Schiff existiert nicht!'));

            $game->setTemplateFile('html/ship.xhtml');

            return;
        }

        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tmark1, seconds: %F", $endTime - $startTime));
        }

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }

        $colony = $this->colonyRepository->getByPosition(
            $ship->getSystem(),
            $ship->getPosX(),
            $ship->getPosY()
        );

        $shipId = $ship->getId();

        $tachyonFresh = $game->getViewContext()['TACHYON_SCAN_JUST_HAPPENED'] ?? false;
        $tachyonActive = $tachyonFresh;

        // check if tachyon scan still active
        if (!$tachyonActive) {
            $tachyonActive = $this->nbsUtility->isTachyonActive($ship);
        }

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

        $game->appendNavigationPart(
            'ship.php',
            _('Schiffe')
        );
        $game->appendNavigationPart(
            sprintf('?%s=1&id=%d', static::VIEW_IDENTIFIER, $shipId),
            $ship->getName()
        );
        $game->setPagetitle($ship->getName());
        $game->setTemplateFile('html/ship.xhtml');

        $game->setTemplateVar('SHIP', $ship);
        if ($starsystem !== null) {
            $game->setTemplateVar('STARSYSTEM_ENTRY_TAL', $starsystem);
        }
        if ($ship->getLss()) {
            $game->setTemplateVar('VISUAL_NAV_PANEL', new VisualNavPanel(
                $ship,
                $game->getUser(),
                $this->loggerUtil,
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
            $neededWorkbees = $ship->getRump()->getNeededWorkbees();

            $game->setTemplateVar('DOCKED', $dockedWorkbees);
            $game->setTemplateVar('NEEDED', $neededWorkbees);
            $game->setTemplateVar('WORKBEECOLOR', $dockedWorkbees < $neededWorkbees ? 'red' : 'green');
        }
        $game->setTemplateVar('PROGRESS', $progress);

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
            $game->setTemplateVar('HAS_STORAGE', new ColonyStorageGoodWrapper($ship->getStorage()));
        }
    }
}
