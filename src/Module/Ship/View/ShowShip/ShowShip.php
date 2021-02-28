<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShip;

use NavPanel;
use request;
use Stu\Component\Player\ColonizationCheckerInterface;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\View\Category\Tal\DatabaseCategoryTalFactoryInterface;
use Stu\Lib\SessionInterface;
use Stu\Module\Ship\Lib\FleetNfsItem;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TachyonScanRepositoryInterface;
use VisualNavPanel;

final class ShowShip implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP';

    private SessionInterface $session;

    private ShipLoaderInterface $shipLoader;

    private FleetRepositoryInterface $fleetRepository;

    private ShipRepositoryInterface $shipRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private ColonizationCheckerInterface $colonizationChecker;

    private DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory;

    private TachyonScanRepositoryInterface $tachyonScanRepository;

    private AstroEntryRepositoryInterface $astroEntryRepository;

    public function __construct(
        SessionInterface $session,
        ShipLoaderInterface $shipLoader,
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository,
        ColonyRepositoryInterface $colonyRepository,
        ColonizationCheckerInterface $colonizationChecker,
        DatabaseCategoryTalFactoryInterface $databaseCategoryTalFactory,
        TachyonScanRepositoryInterface $tachyonScanRepository,
        AstroEntryRepositoryInterface $astroEntryRepository
    ) {
        $this->session = $session;
        $this->shipLoader = $shipLoader;
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
        $this->colonyRepository = $colonyRepository;
        $this->colonizationChecker = $colonizationChecker;
        $this->databaseCategoryTalFactory = $databaseCategoryTalFactory;
        $this->tachyonScanRepository = $tachyonScanRepository;
        $this->astroEntryRepository = $astroEntryRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $ownsCurrentColony = false;

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

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

        $nbs = $this->shipRepository->getSingleShipScannerResults(
            $ship,
            true,
            $tachyonActive
        );

        $singleShipsNbs = $this->shipRepository->getSingleShipScannerResults(
            $ship,
            false,
            $tachyonActive
        );

        $fleets = $this->fleetRepository->getByPositition(
            $ship->getSystem(),
            $ship->getCx(),
            $ship->getCy(),
            $ship->getSx(),
            $ship->getSy()
        );

        $fnbs = [];
        foreach ($fleets as $fleet) {

            $fleetNfsItem = new FleetNfsItem(
                $this->session,
                $fleet,
                $ship,
                $tachyonActive
            );

            if ($fleetNfsItem->getVisibleShips()->count() > 0) {
                if ($fleetNfsItem->isFleetOfCurrentShip()) {
                    array_unshift($fnbs, $fleetNfsItem);
                } else {
                    $fnbs[] = $fleetNfsItem;
                }
            }
        }

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
        $game->setTemplateVar('VISUAL_NAV_PANEL', new VisualNavPanel($ship, $game->getUser(), $ship->getTachyonState(), $tachyonFresh));
        $game->setTemplateVar('NAV_PANEL', new NavPanel($ship));
        $game->setTemplateVar(
            'HAS_NBS',
            $fnbs !== [] || $nbs !== [] || $singleShipsNbs !== []
        );

        $game->setTemplateVar('ASTRO_STATE', $this->getAstroState($ship));
        if ($ship->getState() === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
            $turnsLeft = AstronomicalMappingEnum::TURNS_TO_FINISH - ($game->getCurrentRound()->getTurn() - $ship->getAstroStartTurn());
            $game->setTemplateVar('ASTRO_LEFT', $turnsLeft);
        }
        $game->setTemplateVar('TACHYON_ACTIVE', $tachyonActive);
        $game->setTemplateVar('CLOAK_NBS', !$tachyonActive && $ship->getTachyonState() && $this->shipRepository->isCloakedShipAtLocation($ship));
        $game->setTemplateVar('FLEET_NBS', $fnbs);
        $game->setTemplateVar('STATION_NBS', $nbs);
        $game->setTemplateVar('SHIP_NBS', $singleShipsNbs);
        $game->setTemplateVar('CAN_COLONIZE_CURRENT_COLONY', $canColonize);
        $game->setTemplateVar('OWNS_CURRENT_COLONY', $ownsCurrentColony);
        $game->setTemplateVar('CURRENT_COLONY', $colony);
    }

    private function getAstroState(ShipInterface $ship)
    {
        $system = $ship->getSystem() !== null ? $ship->getSystem() : $ship->isOverSystem();

        if ($system === null) {
            $state = AstronomicalMappingEnum::NONE;
        } else {
            $astroEntry = $this->astroEntryRepository->getByUserAndSystem($ship->getUserId(), $system->getId());

            if ($astroEntry === null) {
                $state = AstronomicalMappingEnum::PLANNABLE;
            } else {
                $state = $astroEntry->getState();
            }
        }
        return new AstroStateWrapper($state);
    }
}
