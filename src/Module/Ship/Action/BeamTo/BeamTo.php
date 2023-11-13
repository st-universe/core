<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\BeamTo;

use request;
use Stu\Lib\BeamUtil\BeamUtilInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Ship\Lib\Interaction\InteractionChecker;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;

final class BeamTo implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BEAMTO';

    private ShipLoaderInterface $shipLoader;

    private BeamUtilInterface $beamUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        BeamUtilInterface $beamUtil
    ) {
        $this->shipLoader = $shipLoader;
        $this->beamUtil = $beamUtil;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $shipId = request::indInt('id');
        $targetId = request::postIntFatal('target');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $ship = $wrapper->get();

        //bad request
        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        if (!InteractionChecker::canInteractWith($ship, $target, $game, true)) {
            return;
        }

        // check for fleet option
        $fleetWrapper = $wrapper->getFleetWrapper();
        if (request::postInt('isfleet') && $fleetWrapper !== null) {
            foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
                $this->beamToTarget(
                    $wrapper,
                    $target,
                    $game
                );
            }
        } else {
            $this->beamToTarget($wrapper, $target, $game);
        }
    }

    private function beamToTarget(ShipWrapperInterface $wrapper, ShipInterface $target, GameControllerInterface $game): void
    {
        $ship = $wrapper->get();
        $epsSystem = $wrapper->getEpsSystemData();

        //sanity checks
        $isDockTransfer = $ship->getDockedTo() === $target || $target->getDockedTo() === $ship;
        if (!$isDockTransfer && ($epsSystem === null || $epsSystem->getEps() === 0)) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->getWarpState()) {
            $game->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        if ($target->getWarpState()) {
            $game->addInformation(sprintf(_('Die %s befindet sich im Warp'), $target->getName()));
            return;
        }
        if ($target->getMaxStorage() <= $target->getStorageSum()) {
            $game->addInformation(sprintf(_('Der Lagerraum der %s ist voll'), $target->getName()));
            return;
        }


        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');

        $shipStorage = $ship->getStorage();

        if ($shipStorage->isEmpty()) {
            $game->addInformation(_("Keine Waren zum Beamen vorhanden"));
            return;
        }
        if (count($commodities) == 0 || count($gcount) == 0) {
            $game->addInformation(_("Es wurden keine Waren zum Beamen ausgewählt"));
            return;
        }
        $game->addInformation(sprintf(
            _('Die %s hat folgende Waren zur %s transferiert'),
            $ship->getName(),
            $target->getName()
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
                $target,
                $game
            );
        }

        $game->sendInformation(
            $target->getUser()->getId(),
            $ship->getUser()->getId(),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
            sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $target->getId())
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
