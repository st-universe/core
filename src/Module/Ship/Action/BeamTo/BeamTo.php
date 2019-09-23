<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\BeamTo;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipStorageManagerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class BeamTo implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BEAMTO';

    private $shipLoader;

    private $shipStorageManager;

    private $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipStorageManagerInterface $shipStorageManager,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipStorageManager = $shipStorageManager;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );
        if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrewCount() == 0) {
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
        $target = $this->shipRepository->find(request::postIntFatal('target'));
        if ($target === null) {
            return;
        }
        if (!$ship->canInteractWith($target)) {
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
        $game->addInformation(sprintf(_('Die %s hat folgende Waren zur %s transferiert'), $ship->getName(),
            $target->getName()));
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
                $game->addInformationf(_('%s ist nicht beambar'), $commodity->getName());
                continue;
            }
            if ($count == "m") {
                $count = $storage->getAmount();
            } else {
                $count = intval($count);
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

            $count = (int) $count;

            $this->shipStorageManager->lowerStorage($ship, $commodity, $count);
            $this->shipStorageManager->upperStorage($target, $commodity, $count);

            $ship->setEps($ship->getEps() - (int)ceil($count / $transferAmount));
        }
        if ($target->getUserId() != $ship->getUserId()) {
            $game->sendInformation($target->getUserId(), $ship->getUserId(), PM_SPECIAL_TRADE);
        }
        $this->shipRepository->save($ship);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
