<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\BeamToColony;

use request;
use Stu\Lib\BeamUtil\BeamUtilInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Ship\Lib\Interaction\InteractionChecker;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class BeamToColony implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BEAMTO_COLONY';

    private ShipLoaderInterface $shipLoader;

    private BeamUtilInterface $beamUtil;

    private ColonyRepositoryInterface $colonyRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        BeamUtilInterface $beamUtil,
        ColonyRepositoryInterface $colonyRepository,
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->beamUtil = $beamUtil;
        $this->colonyRepository = $colonyRepository;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        $colony = $this->colonyRepository->find(request::postIntFatal('target'));
        if ($colony === null || !InteractionChecker::canInteractWith($ship, $colony, $game)) {
            return;
        }

        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');
        if (count($commodities) == 0 || count($gcount) == 0) {
            $game->addInformation(_("Es wurden keine Waren zum Beamen ausgewählt"));
            return;
        }

        if ($colony->getUserId() !== $userId && $this->colonyLibFactory->createColonyShieldingManager($colony)->isShieldingEnabled() && $colony->getShieldFrequency() !== 0) {
            $frequency = request::postInt('frequency');
            if ($frequency !== $colony->getShieldFrequency()) {
                $game->addInformation(_("Die Schildfrequenz ist nicht korrekt"));
                return;
            }
        }

        // check for fleet option
        $fleetWrapper = $wrapper->getFleetWrapper();
        if (request::postInt('isfleet') && $fleetWrapper !== null) {
            foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
                $this->beamToTarget(
                    $wrapper,
                    $colony,
                    $game
                );
            }
        } else {
            $this->beamToTarget($wrapper, $colony, $game);
        }
    }

    private function beamToTarget(ShipWrapperInterface $wrapper, ColonyInterface $colony, GameControllerInterface $game): void
    {
        $ship = $wrapper->get();
        $epsSystem = $wrapper->getEpsSystemData();

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }
        if ($epsSystem === null || $epsSystem->getEps() == 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->getShieldState()) {
            $game->addInformation(_('Die Schilde sind aktiviert'));
            return;
        }
        if ($ship->getWarpState()) {
            $game->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }

        if (!$colony->storagePlaceLeft()) {
            $game->addInformation(sprintf(_('Der Lagerraum der Kolonie %s ist voll'), $colony->getName()));
            return;
        }
        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');

        $shipStorage = $ship->getStorage();

        if ($shipStorage->isEmpty()) {
            $game->addInformation(_("Keine Waren zum Beamen vorhanden"));
            return;
        }
        $game->addInformation(sprintf(
            _('Die %s hat folgende Waren zur Kolonie %s transferiert'),
            $ship->getName(),
            $colony->getName()
        ));
        foreach ($commodities as $key => $value) {
            $commodityId = (int) $value;

            if (!array_key_exists($key, $gcount)) {
                continue;
            }

            $this->beamUtil->transferCommodity(
                $commodityId,
                $gcount[$key],
                $wrapper,
                $wrapper->get(),
                $colony,
                $game
            );
        }

        $game->sendInformation(
            $colony->getUser()->getId(),
            $ship->getUser()->getId(),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
            sprintf(_('colony.php?%s=1&id=%d'), ShowColony::VIEW_IDENTIFIER, $colony->getId())
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
