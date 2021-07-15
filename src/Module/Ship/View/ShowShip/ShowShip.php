<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShip;

use NavPanel;
use request;
use Stu\Component\Player\ColonizationCheckerInterface;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Exception\ShipDoesNotExistException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\View\Category\Tal\DatabaseCategoryTalFactoryInterface;
use Stu\Lib\SessionInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\FleetNfsIterator;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipNfsIterator;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TachyonScanRepositoryInterface;
use VisualNavPanel;

final class ShowShip implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP';

    private SessionInterface $session;

    private LoggerUtilInterface $loggerUtil;

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private ColonizationCheckerInterface $colonizationChecker;

    private DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory;

    private TachyonScanRepositoryInterface $tachyonScanRepository;

    private AstroEntryRepositoryInterface $astroEntryRepository;

    private DatabaseUserRepositoryInterface $databaseUserRepository;

    public function __construct(
        SessionInterface $session,
        LoggerUtilInterface $loggerUtil,
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ColonyRepositoryInterface $colonyRepository,
        ColonizationCheckerInterface $colonizationChecker,
        DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory,
        TachyonScanRepositoryInterface $tachyonScanRepository,
        AstroEntryRepositoryInterface $astroEntryRepository,
        DatabaseUserRepositoryInterface $databaseUserRepository
    ) {
        $this->session = $session;
        $this->loggerUtil = $loggerUtil;
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->colonyRepository = $colonyRepository;
        $this->colonizationChecker = $colonizationChecker;
        $this->databaseCategoryTalFactory = $databaseCategoryTalFactory;
        $this->tachyonScanRepository = $tachyonScanRepository;
        $this->astroEntryRepository = $astroEntryRepository;
        $this->databaseUserRepository = $databaseUserRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $this->loggerUtil->init();

        $user = $game->getUser();
        $userId = $user->getId();
        $ownsCurrentColony = false;

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
            $tachyonActive = !empty($this->tachyonScanRepository->findActiveByShipLocationAndOwner($ship));
        }

        $stationNbs = new ShipNfsIterator($this->shipRepository->getSingleShipScannerResults(
            $ship,
            true,
            $tachyonActive
        ), $userId);

        $singleShipsNbs = new ShipNfsIterator($this->shipRepository->getSingleShipScannerResults(
            $ship,
            false,
            $tachyonActive
        ), $userId);

        $fleetNbs = new FleetNfsIterator(
            $this->shipRepository->getFleetShipsScannerResults($ship, $tachyonActive),
            $ship,
            $this->session
        );

        $canColonize = false;
        if ($colony) {
            if ($ship->getRump()->hasSpecialAbility(ShipRumpSpecialAbilityEnum::COLONIZE)) {
                $canColonize = $this->colonizationChecker->canColonize($user, $colony);
            }
            $ownsCurrentColony = $colony->getUser() === $user;
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
        $game->setTemplateVar('VISUAL_NAV_PANEL', new VisualNavPanel(
            $ship,
            $game->getUser(),
            $this->loggerUtil,
            $ship->getTachyonState(),
            $tachyonFresh
        ));
        $game->setTemplateVar('NAV_PANEL', new NavPanel($ship));
        $game->setTemplateVar(
            'HAS_NBS',
            $fleetNbs->count() > 0 || $stationNbs->count() > 0 || $singleShipsNbs->count() > 0
        );

        $game->setTemplateVar('ASTRO_STATE', $this->getAstroState($ship, $game));
        $game->setTemplateVar('TACHYON_ACTIVE', $tachyonActive);
        $game->setTemplateVar('CLOAK_NBS', !$tachyonActive && $ship->getTachyonState() && $this->shipRepository->isCloakedShipAtLocation($ship));
        $game->setTemplateVar('FLEET_NBS', $fleetNbs);
        $game->setTemplateVar('STATION_NBS', $stationNbs->count() > 0 ? $stationNbs : null);
        $game->setTemplateVar('SHIP_NBS', $singleShipsNbs->count() > 0 ? $singleShipsNbs : null);
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
                $astroEntry = $this->astroEntryRepository->getByUserAndSystem($ship->getUserId(), $system->getId());

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
}
