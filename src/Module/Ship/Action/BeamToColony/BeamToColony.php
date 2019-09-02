<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\BeamToColony;

use Colony;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use SystemActivationWrapper;

final class BeamToColony implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BEAMTO_COLONY';

    private $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $wrapper = new SystemActivationWrapper($ship);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        if ($ship->getEps() == 0) {
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
        $target = new Colony(request::postIntFatal('target'));
        if (!$ship->canInteractWith($target, true)) {
            return;
        }
        if (!$target->storagePlaceLeft()) {
            $game->addInformation(sprintf(_('Der Lagerraum der Kolonie %s ist voll'), $target->getName()));
            return;
        }
        $goods = request::postArray('goods');
        $gcount = request::postArray('count');

        $shipStorage = $ship->getStorage();

        if ($shipStorage === []) {
            $game->addInformation(_("Keine Waren zum Beamen vorhanden"));
            return;
        }
        if (count($goods) == 0 || count($gcount) == 0) {
            $game->addInformation(_("Es wurde keine Waren zum Beamen ausgewählt"));
            return;
        }
        $game->addInformation(sprintf(_('Die %s hat folgende Waren zur Kolonie %s transferiert'),
            $ship->getName(), $target->getName()));
        foreach ($goods as $key => $value) {
            $commodityId = (int) $value;

            if ($ship->getEps() < 1) {
                break;
            }
            if (!array_key_exists($key, $gcount) || $gcount[$key] < 1) {
                continue;
            }

            $storage = $shipStorage[$commodityId] ?? null;

            if ($storage === null) {
                continue;
            }
            $count = $gcount[$key];

            $commodity = $storage->getCommodity();

            if (!$commodity->isBeamable()) {
                $game->addInformation(sprintf(_('%s ist nicht beambar'), $commodity->getName()));
                continue;
            }
            if ($count == "m") {
                $count = $storage->getAmount();
            } else {
                $count = (int) $count;
            }
            if ($count < 1) {
                continue;
            }
            if ($target->getStorageSum() >= $target->getMaxStorage()) {
                break;
            }
            $count = min($count, $storage->getAmount());

            $transferAmount = $commodity->getTransferCount();

            if (ceil($count / $transferAmount) > $ship->getEps()) {
                $count = $ship->getEps() * $transferAmount;
            }
            if ($target->getStorageSum() + $count > $target->getMaxStorage()) {
                $count = $target->getMaxStorage() - $target->getStorageSum();
            }

            $game->addInformation(sprintf(_('%d %s (Energieverbrauch: %d)'), $count, $commodity->getName(),
                ceil($count / $transferAmount)));

            $ship->lowerStorage($commodityId, $count);
            $target->upperStorage($value, $count);
            $ship->lowerEps(ceil($count / $transferAmount));
            $target->setStorageSum($target->getStorageSum() + $count);
        }
        if ($target->getUserId() != $ship->getUserId()) {
            $game->sendInformation($target->getUserId(), $ship->getUserId(), PM_SPECIAL_TRADE);
        }
        $ship->save();
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
