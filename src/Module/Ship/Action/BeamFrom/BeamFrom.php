<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\BeamFrom;

use ObjectNotFoundException;
use request;
use ShipData;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class BeamFrom implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BEAMFROM';

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
        if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrew() == 0) {
            $game->addInformationf(
                _("Es werden %d Crewmitglieder benötigt"),
                $ship->getBuildplan()->getCrew()
            );
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
        if ($ship->getWarpState()) {
            $game->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        if ($ship->getShieldState()) {
            $game->addInformation(_("Die Schilde sind aktiviert"));
            return;
        }
        $target = $this->shipLoader->getById(request::postIntFatal('target'));
        if (!$ship->canInteractWith($target)) {
            return;
        }
        if ($target->getWarpState()) {
            $game->addInformation(sprintf(_('Die %s befindet sich im Warp'), $target->getName()));
            return;
        }
        if (!$ship->storagePlaceLeft()) {
            $game->addInformation(sprintf(_('Der Lagerraum der %s ist voll'), $ship->getName()));
            return;
        }
        $goods = request::postArray('goods');
        $gcount = request::postArray('count');
        if ($target->getStorage()->count() == 0) {
            $game->addInformation(_("Keine Waren zum Beamen vorhanden"));
            return;
        }
        if (count($goods) == 0 || count($gcount) == 0) {
            $game->addInformation(_("Es wurde keine Waren zum Beamen ausgewählt"));
            return;
        }
        $game->addInformation(sprintf(_('Die %s hat folgende Waren von der %s transferiert'),
            $ship->getName(), $target->getName()));
        foreach ($goods as $key => $value) {
            if ($ship->getEps() < 1) {
                break;
            }
            if (!array_key_exists($key, $gcount) || $gcount[$key] < 1) {
                continue;
            }
            if (!$target->getStorage()->offsetExists($value)) {
                continue;
            }
            $count = $gcount[$key];
            $good = $target->getStorage()->offsetGet($value);
            if (!$good->getGood()->isBeamable()) {
                $game->addInformationf(_('%s ist nicht beambar'), $good->getGood()->getName());
                continue;
            }
            if ($count == "m") {
                $count = $good->getAmount();
            } else {
                $count = intval($count);
            }
            if ($count < 1) {
                continue;
            }
            if ($ship->getStorageSum() >= $ship->getMaxStorage()) {
                break;
            }
            if ($count > $good->getAmount()) {
                $count = $good->getAmount();
            }
            if (ceil($count / $good->getGood()->getTransferCount()) > $ship->getEps()) {
                $count = $ship->getEps() * $good->getGood()->getTransferCount();
            }
            if ($ship->getStorageSum() + $count > $ship->getMaxStorage()) {
                $count = $ship->getMaxStorage() - $ship->getStorageSum();
            }
            $game->addInformation(sprintf(_('%d %s (Energieverbrauch: %d)'), $count, $good->getGood()->getName(),
                ceil($count / $good->getGood()->getTransferCount())));

            $target->lowerStorage($value, $count);
            $ship->upperStorage($value, $count);
            $ship->lowerEps(ceil($count / $good->getGood()->getTransferCount()));
            $ship->setStorageSum($ship->getStorageSum() + $count);
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
