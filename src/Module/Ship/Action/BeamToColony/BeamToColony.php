<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\BeamToColony;

use request;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class BeamToColony implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BEAMTO_COLONY';

    private ShipLoaderInterface $shipLoader;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipStorageManagerInterface $shipStorageManager;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository,
        ShipStorageManagerInterface $shipStorageManager,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
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

        if (!$ship->hasEnoughCrew($game)) {
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
        $target = $this->colonyRepository->find((int) request::postIntFatal('target'));
        if ($target === null || !$ship->canInteractWith($target, true)) {
            return;
        }
        if ($target->getUserId() !== $userId && $target->getShieldState()) {
            if ($target->getShieldFrequency() !== 0) {
                $frequency = (int) request::postInt('frequency');

                if ($frequency !== $target->getShieldFrequency()) {
                    $game->addInformation(_("Die Schildfrequenz ist nicht korrekt"));
                    return;
                }
            }
        }
        if (!$target->storagePlaceLeft()) {
            $game->addInformation(sprintf(_('Der Lagerraum der Kolonie %s ist voll'), $target->getName()));
            return;
        }
        $goods = request::postArray('goods');
        $gcount = request::postArray('count');

        $shipStorage = $ship->getStorage();

        if ($shipStorage->isEmpty()) {
            $game->addInformation(_("Keine Waren zum Beamen vorhanden"));
            return;
        }
        if (count($goods) == 0 || count($gcount) == 0) {
            $game->addInformation(_("Es wurden keine Waren zum Beamen ausgewählt"));
            return;
        }
        $game->addInformation(sprintf(
            _('Die %s hat folgende Waren zur Kolonie %s transferiert'),
            $ship->getName(),
            $target->getName()
        ));
        foreach ($goods as $key => $value) {
            $commodityId = (int) $value;

            if ($ship->getEps() < 1) {
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

            if (!$commodity->isBeamable()) {
                $game->addInformation(sprintf(_('%s ist nicht beambar'), $commodity->getName()));
                continue;
            }
            if ($count == "max") {
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

            $transferAmount = $commodity->getTransferCount() * $ship->getBeamFactor();

            if (ceil($count / $transferAmount) > $ship->getEps()) {
                $count = $ship->getEps() * $transferAmount;
            }
            if ($target->getStorageSum() + $count > $target->getMaxStorage()) {
                $count = $target->getMaxStorage() - $target->getStorageSum();
            }

            $game->addInformationf(
                _('%d %s (Energieverbrauch: %d)'),
                $count,
                $commodity->getName(),
                ceil($count / $transferAmount)
            );

            $count = (int) $count;

            $this->shipStorageManager->lowerStorage($ship, $commodity, $count);
            $this->colonyStorageManager->upperStorage($target, $commodity, $count);

            $ship->setEps($ship->getEps() - (int) ceil($count / $transferAmount));
        }
        $game->sendInformation(
            $target->getUser()->getId(),
            $ship->getUser()->getId(),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
            sprintf(_('colony.php?SHOW_COLONY=1&id=%d'), $target->getId())
        );

        $this->shipRepository->save($ship);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
