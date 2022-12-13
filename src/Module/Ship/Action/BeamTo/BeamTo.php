<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\BeamTo;

use request;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class BeamTo implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BEAMTO';

    private ShipLoaderInterface $shipLoader;

    private ShipStorageManagerInterface $shipStorageManager;

    private ShipRepositoryInterface $shipRepository;

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

        $shipId = request::indInt('id');
        $targetId = request::postIntFatal('target');

        $shipArray = $this->shipLoader->getByIdAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $ship = $shipArray[$shipId];
        $target = $shipArray[$targetId];

        //bad request
        if (!$ship->hasEnoughCrew($game)) {
            return;
        }
        if ($target === null) {
            return;
        }
        if (!$ship->canInteractWith($target, false, true)) {
            return;
        }

        // check for fleet option
        if (request::postInt('isfleet') && $ship->getFleet() !== null) {
            foreach ($ship->getFleet()->getShips() as $ship) {
                $this->beamToTarget($ship, $target, $game);
            }
        } else {
            $this->beamToTarget($ship, $target, $game);
        }
    }

    private function beamToTarget(ShipInterface $ship, ShipInterface $target, GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        //sanity checks
        $isDockTransfer = $ship->getDockedTo() === $target || $target->getDockedTo() === $ship;
        if (!$isDockTransfer && $ship->getEps() == 0) {
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

            if (!$isDockTransfer && $ship->getEps() < 1) {
                break;
            }
            if (!array_key_exists($key, $gcount)) {
                continue;
            }

            $storage = $shipStorage[$commodityId] ?? null;

            if ($storage === null) {
                continue;
            }
            $count = $gcount[$key];

            $commodity = $storage->getCommodity();

            if (!$commodity->isBeamable($userId, $target->getUser()->getId())) {
                $game->addInformationf(_('%s ist nicht beambar'), $commodity->getName());
                continue;
            }
            if ($count == "max") {
                $count = $storage->getAmount();
            } else {
                $count = (int)$count;
            }
            if ($count < 1) {
                continue;
            }
            if ($target->getStorageSum() >= $target->getMaxStorage()) {
                break;
            }
            $count = min($count, $storage->getAmount());

            $transferAmount = $commodity->getTransferCount() * $ship->getBeamFactor();

            if (!$isDockTransfer && ceil($count / $transferAmount) > $ship->getEps()) {
                $count = $ship->getEps() * $transferAmount;
            }
            if ($target->getStorageSum() + $count > $target->getMaxStorage()) {
                $count = $target->getMaxStorage() - $target->getStorageSum();
            }
            $game->addInformation(sprintf(
                _('%d %s (Energieverbrauch: %d)'),
                $count,
                $commodity->getName(),
                $isDockTransfer ? 0 : ceil($count / $transferAmount)
            ));

            $count = (int) $count;

            $this->shipStorageManager->lowerStorage($ship, $commodity, $count);
            $this->shipStorageManager->upperStorage($target, $commodity, $count);

            if (!$isDockTransfer) {
                $ship->setEps($ship->getEps() - (int)ceil($count / $transferAmount));
            }
        }

        $game->sendInformation(
            $target->getUser()->getId(),
            $ship->getUser()->getId(),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
            sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $target->getId())
        );
        $this->shipRepository->save($ship);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
