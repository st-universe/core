<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\BeamFromColony;

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

final class BeamFromColony implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BEAMFROM_COLONY';

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
        if ($ship->getMaxStorage() <= $ship->getStorageSum()) {
            $game->addInformation(sprintf(_('Der Lagerraum der %s ist voll'), $ship->getName()));
            return;
        }
        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');
        if ($target->getStorage()->isEmpty()) {
            $game->addInformation(_("Keine Waren zum Beamen vorhanden"));
            return;
        }
        if (count($commodities) == 0 || count($gcount) == 0) {
            $game->addInformation(_("Es wurden keine Waren zum Beamen ausgewählt"));
            return;
        }
        $game->addInformation(sprintf(
            _('Die %s hat folgende Waren von der Kolonie %s transferiert'),
            $ship->getName(),
            $target->getName()
        ));
        foreach ($commodities as $key => $value) {
            $value = (int) $value;
            if ($ship->getEps() < 1) {
                break;
            }
            if (!array_key_exists($key, $gcount)) {
                continue;
            }
            $commodity = $target->getStorage()[$value] ?? null;
            if ($commodity === null) {
                continue;
            }
            $count = $gcount[$key];
            if (!$commodity->getCommodity()->isBeamable($userId, $target->getUser()->getId())) {
                $game->addInformation(sprintf(_('%s ist nicht beambar'), $commodity->getCommodity()->getName()));
                continue;
            }
            if ($count == "max") {
                $count = (int) $commodity->getAmount();
            } else {
                $count = (int) $count;
            }
            if ($count < 1) {
                continue;
            }
            if ($ship->getStorageSum() >= $ship->getMaxStorage()) {
                break;
            }
            if ($count > $commodity->getAmount()) {
                $count = (int) $commodity->getAmount();
            }

            $transferAmount = $commodity->getCommodity()->getTransferCount() * $ship->getBeamFactor();

            if (ceil($count / $transferAmount) > $ship->getEps()) {
                $count = $ship->getEps() * $transferAmount;
            }
            if ($ship->getStorageSum() + $count > $ship->getMaxStorage()) {
                $count = $ship->getMaxStorage() - $ship->getStorageSum();
            }

            $count = (int) $count;

            $game->addInformationf(
                _('%d %s (Energieverbrauch: %d)'),
                $count,
                $commodity->getCommodity()->getName(),
                ceil($count / $transferAmount)
            );

            $count = (int) $count;

            $this->colonyStorageManager->lowerStorage($target, $commodity->getCommodity(), $count);
            $this->shipStorageManager->upperStorage($ship, $commodity->getCommodity(), $count);

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
